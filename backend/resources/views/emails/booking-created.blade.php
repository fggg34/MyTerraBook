<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Booking Confirmation</title></head>
<body>
  <h2>Booking Confirmed: {{ $booking->reference }}</h2>
  <p>Hi {{ $booking->customer_name }}, your booking has been created.</p>
  <p>Status: {{ $booking->status->value }}</p>
  <p>Pickup: {{ $booking->pickup_at }}</p>
  <p>Dropoff: {{ $booking->dropoff_at }}</p>
  <p>Total: {{ $booking->total }} {{ $booking->currency }}</p>
</body>
</html>
