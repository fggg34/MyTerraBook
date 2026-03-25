<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Booking Status Updated</title></head>
<body>
  <h2>Booking Update: {{ $booking->reference }}</h2>
  <p>Hello {{ $booking->customer_name }}, your booking status changed to <strong>{{ $booking->status->value }}</strong>.</p>
  <p>Total: {{ $booking->total }} {{ $booking->currency }}</p>
  @if($booking->notes)
    <p>Notes: {{ $booking->notes }}</p>
  @endif
</body>
</html>
