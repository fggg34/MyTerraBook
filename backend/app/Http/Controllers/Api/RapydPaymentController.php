<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendBookingConfirmationEmail;
use App\Models\GuestHouseBooking;
use App\Models\RapydPayment;
use App\Models\RapydWebhookLog;
use App\Services\RapydService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class RapydPaymentController extends Controller
{
    public function __construct(private readonly RapydService $rapyd) {}

    /**
     * Start a hosted checkout for the 20% platform fee. The remaining 80% is
     * collected in cash by the host on arrival and is never processed by Rapyd.
     */
    public function initiateCheckout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => ['required', 'integer'],
            'total_price' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['nullable', 'string', 'size:3'],
            'host_id' => ['nullable', 'integer'],
        ]);

        $booking = GuestHouseBooking::query()->find($validated['order_id']);

        $commissionRate = (float) config('rapyd.commission_rate', 0.20);
        $totalPrice = round((float) $validated['total_price'], 2);
        $platformFee = round($totalPrice * $commissionRate, 2);
        $cashDueOnArrival = round($totalPrice - $platformFee, 2);
        $currency = strtoupper($validated['currency'] ?? (string) config('rapyd.currency', 'USD'));

        $hostId = $validated['host_id'] ?? $booking?->guestHouse?->user_id;
        $userId = $request->user()?->id ?? $booking?->user_id;

        $metadata = [
            'order_id' => (string) $validated['order_id'],
            'user_id' => (string) ($userId ?? ''),
            'host_id' => (string) ($hostId ?? ''),
            'total_price' => (string) $totalPrice,
            'platform_fee' => (string) $platformFee,
            'cash_due_on_arrival' => (string) $cashDueOnArrival,
        ];

        try {
            $checkout = $this->rapyd->createCheckoutPage([
                'amount' => $platformFee, // ONLY the 20% platform fee is charged online.
                'currency' => $currency,
                'country' => config('rapyd.country', 'US'),
                'payment_method_types' => config('rapyd.payment_method_types'),
                'merchant_reference_id' => (string) $validated['order_id'],
                'metadata' => $metadata,
            ]);
        } catch (Throwable $e) {
            Log::error('Rapyd initiateCheckout failed', ['error' => $e->getMessage()]);

            return response()->json(['message' => 'Could not start card payment. Please try again.'], 502);
        }

        $payment = RapydPayment::create([
            'order_id' => $validated['order_id'],
            'user_id' => $userId,
            'host_id' => $hostId,
            'checkout_id' => $checkout['checkout_id'],
            'payment_id' => null,
            'total_price' => $totalPrice,
            'platform_fee' => $platformFee,
            'cash_due_on_arrival' => $cashDueOnArrival,
            'currency' => $currency,
            'status' => 'pending',
            'metadata' => $metadata,
        ]);

        if ($booking) {
            $booking->forceFill([
                'total_price' => $totalPrice,
                'platform_fee' => $platformFee,
                'cash_due_on_arrival' => $cashDueOnArrival,
                'payment_status' => 'pending',
                'payment_method' => 'rapyd_card',
                'rapyd_checkout_id' => $checkout['checkout_id'],
            ])->save();
        }

        return response()->json([
            'checkout_url' => $checkout['redirect_url'],
            'checkout_id' => $checkout['checkout_id'],
            'order_id' => (int) $validated['order_id'],
            'total_price' => $totalPrice,
            'platform_fee' => $platformFee,        // 20% — charged now
            'cash_due_on_arrival' => $cashDueOnArrival, // 80% — cash at location
            'currency' => $currency,
            'payment_id' => $payment->id,
        ]);
    }

    /**
     * Poll the status of a checkout and return the full split breakdown.
     */
    public function checkoutStatus(Request $request, string $checkoutId): JsonResponse
    {
        $payment = RapydPayment::query()->where('checkout_id', $checkoutId)->first();

        try {
            $remote = $this->rapyd->getCheckoutStatus($checkoutId);
        } catch (Throwable $e) {
            Log::warning('Rapyd checkoutStatus failed', ['checkout_id' => $checkoutId, 'error' => $e->getMessage()]);
            $remote = [];
        }

        $paymentStatus = data_get($remote, 'payment.status') ?? data_get($remote, 'status');

        return response()->json([
            'checkout_id' => $checkoutId,
            'order_id' => $payment?->order_id,
            'status' => $payment?->status ?? 'pending',
            'rapyd_status' => $paymentStatus,
            'total_price' => $payment?->total_price,
            'platform_fee' => $payment?->platform_fee,
            'cash_due_on_arrival' => $payment?->cash_due_on_arrival,
            'currency' => $payment?->currency,
            'paid_at' => $payment?->paid_at,
        ]);
    }

    /**
     * Rapyd webhook receiver. Always returns 200 quickly so Rapyd stops retrying.
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        $verified = $this->rapyd->verifyWebhook($request);
        $payload = $request->json()->all();

        $eventType = data_get($payload, 'type', '');
        $data = data_get($payload, 'data', []);
        $checkoutId = data_get($data, 'id') ?? data_get($data, 'checkout_id');
        $paymentId = data_get($data, 'payment') ?? data_get($data, 'id');

        RapydWebhookLog::create([
            'event_type' => $eventType,
            'checkout_id' => is_string($checkoutId) ? $checkoutId : null,
            'payment_id' => is_string($paymentId) ? $paymentId : null,
            'payload' => $payload,
            'processed_at' => $verified ? now() : null,
        ]);

        if (! $verified) {
            Log::warning('Rapyd webhook signature verification failed', ['event' => $eventType]);

            return response()->json(['received' => true], 200);
        }

        $merchantReference = data_get($data, 'merchant_reference_id');
        $payment = RapydPayment::query()
            ->when(is_string($checkoutId), fn ($q) => $q->where('checkout_id', $checkoutId))
            ->when(! is_string($checkoutId) && $merchantReference, fn ($q) => $q->where('order_id', $merchantReference))
            ->first();

        switch ($eventType) {
            case 'PAYMENT_COMPLETED':
            case 'CHECKOUT_COMPLETED':
                if ($payment && $payment->status !== 'paid') {
                    $payment->update([
                        'status' => 'paid',
                        'payment_id' => is_string($paymentId) ? $paymentId : $payment->payment_id,
                        'paid_at' => now(),
                    ]);

                    $this->updateBooking($payment, 'partially_paid', is_string($paymentId) ? $paymentId : null);
                    SendBookingConfirmationEmail::dispatch($payment->id);
                }
                break;

            case 'PAYMENT_FAILED':
                if ($payment) {
                    $payment->update(['status' => 'failed']);
                    $this->updateBooking($payment, 'pending');
                }
                break;

            case 'PAYMENT_REFUND_COMPLETED':
                if ($payment) {
                    $payment->update(['status' => 'refunded']);
                }
                break;
        }

        return response()->json(['received' => true], 200);
    }

    /**
     * Host marks the 80% cash balance as received on arrival.
     */
    public function confirmCashReceived(Request $request, int $orderId): JsonResponse
    {
        $booking = GuestHouseBooking::query()->findOrFail($orderId);

        $hostId = $booking->guestHouse?->user_id;
        if ($hostId !== null && $request->user()?->id !== $hostId) {
            return response()->json(['message' => 'You are not the host for this booking.'], 403);
        }

        $booking->forceFill([
            'payment_status' => 'confirmed',
            'cash_received_at' => now(),
        ])->save();

        $payment = $booking->rapydPayments()->latest()->first();
        if ($payment) {
            SendBookingConfirmationEmail::dispatch($payment->id, notifyCashReceived: true);
        }

        return response()->json([
            'order_id' => $booking->id,
            'payment_status' => $booking->payment_status,
            'cash_received_at' => $booking->cash_received_at,
        ]);
    }

    /**
     * Admin list of all Rapyd payments with the split breakdown.
     */
    public function listPayments(Request $request): JsonResponse
    {
        $payments = RapydPayment::query()
            ->with(['user:id,name,email', 'host:id,name,email', 'booking:id,booking_reference,guest_house_id'])
            ->latest()
            ->paginate((int) $request->integer('per_page', 25));

        return response()->json($payments);
    }

    private function updateBooking(RapydPayment $payment, string $paymentStatus, ?string $paymentId = null): void
    {
        $booking = GuestHouseBooking::query()->find($payment->order_id);
        if (! $booking) {
            return;
        }

        $attrs = ['payment_status' => $paymentStatus];
        if ($paymentId !== null) {
            $attrs['rapyd_payment_id'] = $paymentId;
        }

        $booking->forceFill($attrs)->save();
    }
}
