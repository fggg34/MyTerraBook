<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Check-in report {{ $order->reference }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 18px; margin-bottom: 0.5rem; }
        h2 { font-size: 14px; margin-top: 1rem; margin-bottom: 0.5rem; }
        table { width: 100%; border-collapse: collapse; margin-top: 0.5rem; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #f5f5f5; }
        .muted { color: #555; }
    </style>
</head>
<body>
    <h1>Vehicle check-in report</h1>
    <p><strong>Reference:</strong> {{ $order->reference }}</p>
    <p><strong>Customer:</strong> {{ $order->customer_name }}, {{ $order->customer_email }}</p>
    <p><strong>Vehicle:</strong> {{ $order->car?->name ?? '-' }}</p>
    <p><strong>Assigned unit ID:</strong> {{ $order->car_unit_id ?? 'Not assigned' }}</p>
    <p><strong>Pick-up:</strong> {{ $order->pickup_at->timezone(config('app.timezone'))->format('Y-m-d H:i') }} ({{ $order->pickupLocation?->name ?? '-' }})</p>
    <p><strong>Drop-off:</strong> {{ $order->dropoff_at->timezone(config('app.timezone'))->format('Y-m-d H:i') }} ({{ $order->dropoffLocation?->name ?? '-' }})</p>

    <h2>Distinctive unit features</h2>
    @if ($order->carUnit && $order->carUnit->distinctiveValues->isNotEmpty())
        <table>
            <thead>
                <tr>
                    <th>Feature</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->carUnit->distinctiveValues as $featureValue)
                    <tr>
                        <td>{{ $featureValue->definition?->name ?? 'Feature' }}</td>
                        <td>{{ $featureValue->value }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="muted">No distinctive features recorded for the assigned unit.</p>
    @endif

    <h2>Recorded damages</h2>
    @if ($order->carUnit && $order->carUnit->damageMarkers->isNotEmpty())
        <table>
            <thead>
                <tr>
                    <th>Diagram</th>
                    <th>Position</th>
                    <th>Description</th>
                    <th>Marked at</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->carUnit->damageMarkers as $marker)
                    <tr>
                        <td>{{ $marker->diagram_key ?? 'car_inspection' }}</td>
                        <td>X {{ $marker->position_x }}, Y {{ $marker->position_y }}</td>
                        <td>{{ $marker->description ?? '-' }}</td>
                        <td>{{ $marker->marked_at ? $marker->marked_at->timezone(config('app.timezone'))->format('Y-m-d H:i') : ',' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="muted">No damage marks recorded for the assigned unit.</p>
    @endif

    <p style="margin-top: 1.5rem;"><strong>Signature (customer):</strong> ______________________</p>
    <p><strong>Signature (operator):</strong> ______________________</p>
</body>
</html>
