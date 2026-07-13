<?php

namespace App\Http\Controllers\Api;

use App\Enums\GuestHouseBookingStatus;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Jobs\SendBookingConfirmationEmail;
use App\Models\GuestHouseBooking;
use App\Models\Order;
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
            'order_type' => ['nullable', 'in:guesthouse,car'],
        ]);

        $orderType = $validated['order_type'] ?? 'guesthouse';
        $booking = $orderType === 'guesthouse'
            ? GuestHouseBooking::query()->find($validated['order_id'])
            : null;

        $currency = strtoupper($validated['currency'] ?? (string) config('rapyd.currency', 'ISK'));
        $decimals = RapydService::decimalsFor($currency);

        $commissionRate = (float) config('rapyd.commission_rate', 0.15);
        $totalPrice = round((float) $validated['total_price'], $decimals);
        // Round the online fee to the currency's precision so the amount charged
        // matches what Rapyd accepts (e.g. whole krónur for ISK).
        $platformFee = round($totalPrice * $commissionRate, $decimals);
        $cashDueOnArrival = round($totalPrice - $platformFee, $decimals);

        $hostId = $validated['host_id'] ?? $booking?->guestHouse?->user_id;
        $userId = $request->user()?->id ?? $booking?->user_id;

        $metadata = [
            'order_id' => (string) $validated['order_id'],
            'order_type' => $orderType,
            'user_id' => (string) ($userId ?? ''),
            'host_id' => (string) ($hostId ?? ''),
            'total_price' => (string) $totalPrice,
            'platform_fee' => (string) $platformFee,
            'cash_due_on_arrival' => (string) $cashDueOnArrival,
        ];

        // Rapyd does not substitute placeholders in the return URL, so we attach
        // the identifiers we already know and let the SPA poll the status by them.
        $returnQuery = http_build_query([
            'order_id' => $validated['order_id'],
            'order_type' => $orderType,
        ]);
        $frontendUrl = (string) config('rapyd.frontend_url');
        $completeUrl = $frontendUrl.config('rapyd.success_path', '/booking/rapyd/success').'?'.$returnQuery;
        $errorUrl = $frontendUrl.config('rapyd.error_path', '/booking/rapyd/failed').'?'.$returnQuery;

        if (! $this->rapyd->isConfigured()) {
            Log::error('Rapyd initiateCheckout failed: credentials missing');

            // Use 503 (not 502): Cloudflare replaces opaque 502 responses and hides our JSON.
            return response()->json([
                'message' => 'Card payment is not configured yet. Please contact support or try another payment method.',
                'error' => config('app.debug')
                    ? 'Missing RAPYD_ACCESS_KEY / RAPYD_SECRET_KEY (or admin Payment Methods keys).'
                    : null,
            ], 503);
        }

        try {
            $checkout = $this->rapyd->createCheckoutPage([
                'amount' => $platformFee, // ONLY the platform fee is charged online.
                'currency' => $currency,
                'country' => config('rapyd.country', 'IS'),
                'payment_method_types' => config('rapyd.payment_method_types'),
                'merchant_reference_id' => (string) $validated['order_id'],
                'complete_payment_url' => $completeUrl,
                'error_payment_url' => $errorUrl,
                'metadata' => $metadata,
            ]);
        } catch (Throwable $e) {
            Log::error('Rapyd initiateCheckout failed', ['error' => $e->getMessage()]);

            // 422 keeps the JSON body visible through Cloudflare (502s get replaced).
            return response()->json([
                'message' => $e->getMessage() !== ''
                    ? $e->getMessage()
                    : 'Could not start card payment. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 422);
        }

        if (empty($checkout['redirect_url'])) {
            Log::error('Rapyd initiateCheckout returned empty redirect_url', [
                'checkout_id' => $checkout['checkout_id'] ?? null,
            ]);

            return response()->json([
                'message' => 'Could not start card payment. Please try again.',
                'error' => config('app.debug') ? 'Rapyd returned an empty redirect_url.' : null,
            ], 422);
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

        return response()->json($this->buildStatusPayload($payment));
    }

    /**
     * Status lookup by the order identifiers we control (used by the SPA return
     * page, since Rapyd cannot echo the checkout id back in the redirect URL).
     */
    public function orderStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => ['required', 'integer'],
            'order_type' => ['nullable', 'in:guesthouse,car'],
        ]);

        $orderType = $validated['order_type'] ?? 'guesthouse';

        $payment = RapydPayment::query()
            ->where('order_id', $validated['order_id'])
            ->where(function ($q) use ($orderType) {
                $q->whereNull('metadata->order_type')
                    ->orWhere('metadata->order_type', $orderType);
            })
            ->latest()
            ->first();

        return response()->json($this->buildStatusPayload($payment));
    }

    /**
     * Fetch the live Rapyd status for a payment, apply the webhook-fallback
     * confirmation, and shape the JSON response consumed by the SPA.
     *
     * @return array<string, mixed>
     */
    private function buildStatusPayload(?RapydPayment $payment): array
    {
        $remote = [];
        if ($payment) {
            try {
                $remote = $this->rapyd->getCheckoutStatus($payment->checkout_id);
            } catch (Throwable $e) {
                Log::warning('Rapyd checkoutStatus failed', [
                    'checkout_id' => $payment->checkout_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $paymentStatus = data_get($remote, 'payment.status') ?? data_get($remote, 'status');

        // Fallback confirmation: if Rapyd reports the fee as paid but our webhook
        // hasn't arrived yet (common on local/sandbox where webhooks can't reach
        // the host), confirm the order here so the guest sees the right state.
        $rapydPaidId = data_get($remote, 'payment.id');
        $rapydPaid = data_get($remote, 'payment.paid') === true
            || in_array((string) data_get($remote, 'payment.status'), ['CLO', 'closed'], true);

        if ($payment && $payment->status !== 'paid' && $rapydPaid) {
            $payment->update([
                'status' => 'paid',
                'payment_id' => is_string($rapydPaidId) ? $rapydPaidId : $payment->payment_id,
                'paid_at' => now(),
            ]);
            $this->confirmOrder($payment, is_string($rapydPaidId) ? $rapydPaidId : null);
            $payment->refresh();
        }

        return [
            'checkout_id' => $payment?->checkout_id,
            'order_id' => $payment?->order_id,
            'status' => $payment?->status ?? 'pending',
            'rapyd_status' => $paymentStatus,
            'total_price' => $payment?->total_price,
            'platform_fee' => $payment?->platform_fee,
            'cash_due_on_arrival' => $payment?->cash_due_on_arrival,
            'currency' => $payment?->currency,
            'paid_at' => $payment?->paid_at,
            // Lets the SPA redirect to the existing confirmation page after payment.
            'confirmation_token' => $payment ? $this->confirmationTokenFor($payment) : null,
        ];
    }

    /**
     * Resolve the confirmation token for the booking/order behind a payment.
     */
    private function confirmationTokenFor(RapydPayment $payment): ?string
    {
        $orderType = (string) data_get($payment->metadata, 'order_type', 'guesthouse');

        return $orderType === 'car'
            ? Order::query()->whereKey($payment->order_id)->value('confirmation_token')
            : GuestHouseBooking::query()->whereKey($payment->order_id)->value('confirmation_token');
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

                    // Online fee received: confirm the previously-held booking/order
                    // and trigger the appropriate confirmation email(s).
                    $this->confirmOrder($payment, is_string($paymentId) ? $paymentId : null);
                }
                break;

            case 'PAYMENT_FAILED':
                if ($payment) {
                    $payment->update(['status' => 'failed']);
                    // Leave the booking/order pending; the guest can retry payment.
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
            try {
                SendBookingConfirmationEmail::dispatchSync($payment->id, notifyCashReceived: true);
            } catch (Throwable $e) {
                Log::error('Rapyd cash-received email failed', [
                    'payment_id' => $payment->id,
                    'error' => $e->getMessage(),
                ]);
            }
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

    /**
     * Confirm the held booking/order once the online fee has been paid.
     * The correct table is resolved from the payment's stored order_type.
     */
    private function confirmOrder(RapydPayment $payment, ?string $paymentId = null): void
    {
        $orderType = (string) data_get($payment->metadata, 'order_type', 'guesthouse');

        if ($orderType === 'car') {
            $order = Order::query()->find($payment->order_id);
            if (! $order) {
                return;
            }
            if ($order->order_status === OrderStatus::Pending) {
                $order->transitionOrderStatus(OrderStatus::Confirmed);
                // Car rentals use their own confirmation email (not the cash-on-arrival split).
                app(\App\Services\Email\OrderEmailNotifier::class)->notifyCreated($order->load('car.host'));
            }

            return;
        }

        $booking = GuestHouseBooking::query()->find($payment->order_id);
        if (! $booking) {
            return;
        }

        $attrs = [
            'payment_status' => 'partially_paid',
            'status' => GuestHouseBookingStatus::Confirmed,
        ];
        if ($booking->confirmed_at === null) {
            $attrs['confirmed_at'] = now();
        }
        if ($paymentId !== null) {
            $attrs['rapyd_payment_id'] = $paymentId;
        }

        $booking->forceFill($attrs)->saveQuietly();

        // Guest houses use the 15%/85% paid-online / cash-on-arrival emails.
        // Sent synchronously so it works without a queue worker (matches the rest
        // of the app); a mail failure must not break payment confirmation.
        try {
            SendBookingConfirmationEmail::dispatchSync($payment->id);
        } catch (Throwable $e) {
            Log::error('Rapyd confirmation email failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
