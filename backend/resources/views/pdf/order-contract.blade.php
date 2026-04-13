<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Rental agreement {{ $order->reference }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 18px; margin-bottom: 0.5rem; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #f5f5f5; }
    </style>
</head>
<body>
    <h1>Rental agreement</h1>
    <p><strong>Reference:</strong> {{ $order->reference }}</p>
    <p><strong>Customer:</strong> {{ $order->customer_name }} — {{ $order->customer_email }}</p>
    <p><strong>Vehicle:</strong> {{ $order->car?->name ?? '—' }}</p>
    <p><strong>Pick-up:</strong> {{ $order->pickup_at->timezone(config('app.timezone'))->format('Y-m-d H:i') }}</p>
    <p><strong>Drop-off:</strong> {{ $order->dropoff_at->timezone(config('app.timezone'))->format('Y-m-d H:i') }}</p>
    <p><strong>Status:</strong> {{ $order->order_status->value }}</p>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th style="text-align:right;">Amount ({{ $order->currency }})</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Vehicle rental</td>
                <td style="text-align:right;">{{ \App\Support\Money::formatDecimalFromCents((int) $order->base_rental_cents) }}</td>
            </tr>
            @if ($order->extras_cents > 0)
                <tr>
                    <td>Add-ons</td>
                    <td style="text-align:right;">{{ \App\Support\Money::formatDecimalFromCents((int) $order->extras_cents) }}</td>
                </tr>
            @endif
            @if ($order->fees_cents > 0)
                <tr>
                    <td>Fees</td>
                    <td style="text-align:right;">{{ \App\Support\Money::formatDecimalFromCents((int) $order->fees_cents) }}</td>
                </tr>
            @endif
            @if ($order->discount_cents > 0)
                <tr>
                    <td>Discount</td>
                    <td style="text-align:right;">-{{ \App\Support\Money::formatDecimalFromCents((int) $order->discount_cents) }}</td>
                </tr>
            @endif
            @if ($order->tax_cents > 0)
                <tr>
                    <td>Tax</td>
                    <td style="text-align:right;">{{ \App\Support\Money::formatDecimalFromCents((int) $order->tax_cents) }}</td>
                </tr>
            @endif
            <tr>
                <td><strong>Total</strong></td>
                <td style="text-align:right;"><strong>{{ \App\Support\Money::formatDecimalFromCents((int) $order->total_cents) }}</strong></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
