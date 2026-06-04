<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Booking confirmation {{ $booking->booking_reference }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 18px; margin-bottom: 0.5rem; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #f5f5f5; }
    </style>
</head>
<body>
    <h1>Guest house booking confirmation</h1>
    <p><strong>Reference:</strong> {{ $booking->booking_reference }}</p>
    <p><strong>Guest:</strong> {{ $booking->guest_name }} — {{ $booking->guest_email }}</p>
    @if ($booking->guest_phone)
        <p><strong>Phone:</strong> {{ $booking->guest_phone }}</p>
    @endif
    <p><strong>Property:</strong> {{ $booking->guestHouse?->name ?? '—' }}</p>
    <p><strong>Address:</strong> {{ $booking->guestHouse?->address ?? '—' }}, {{ $booking->guestHouse?->city ?? '' }}</p>
    <p><strong>Check-in:</strong> {{ $booking->check_in->format('Y-m-d') }} from {{ $booking->guestHouse?->check_in_time ?? '15:00' }}</p>
    <p><strong>Check-out:</strong> {{ $booking->check_out->format('Y-m-d') }} by {{ $booking->guestHouse?->check_out_time ?? '11:00' }}</p>
    <p><strong>Guests:</strong> {{ $booking->guests_count }} · <strong>Nights:</strong> {{ $booking->nights }}</p>
    <p><strong>Cancellation policy:</strong> {{ $booking->guestHouse?->cancellation_policy?->value ?? 'moderate' }}</p>

    @if ($booking->special_requests)
        <p><strong>Special requests:</strong> {{ $booking->special_requests }}</p>
    @endif

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th style="text-align:right;">Amount ({{ $currency }})</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Accommodation ({{ $booking->nights }} nights)</td>
                <td style="text-align:right;">{{ \App\Support\Money::formatDecimalFromCents((int) $booking->base_total) }}</td>
            </tr>
            @if ($booking->cleaning_fee > 0)
                <tr>
                    <td>Cleaning fee</td>
                    <td style="text-align:right;">{{ \App\Support\Money::formatDecimalFromCents((int) $booking->cleaning_fee) }}</td>
                </tr>
            @endif
            @if ($booking->discount_amount > 0)
                <tr>
                    <td>Discount</td>
                    <td style="text-align:right;">-{{ \App\Support\Money::formatDecimalFromCents((int) $booking->discount_amount) }}</td>
                </tr>
            @endif
            @if ($booking->tax_amount > 0)
                <tr>
                    <td>Tax</td>
                    <td style="text-align:right;">{{ \App\Support\Money::formatDecimalFromCents((int) $booking->tax_amount) }}</td>
                </tr>
            @endif
            <tr>
                <td><strong>Total due</strong></td>
                <td style="text-align:right;"><strong>{{ \App\Support\Money::formatDecimalFromCents((int) $booking->total_amount) }}</strong></td>
            </tr>
            @if ($booking->security_deposit > 0)
                <tr>
                    <td>Security deposit (refundable)</td>
                    <td style="text-align:right;">{{ \App\Support\Money::formatDecimalFromCents((int) $booking->security_deposit) }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <p style="margin-top:2rem;font-size:10px;color:#666;">
        Thank you for booking with {{ config('app.name') }}. For assistance, contact our support team.
    </p>
</body>
</html>
