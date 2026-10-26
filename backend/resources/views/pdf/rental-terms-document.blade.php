<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Rental terms {{ $reference }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 18px; margin-bottom: 0.25rem; }
        h2 { font-size: 14px; margin-top: 1.25rem; }
        p { margin: 0.25rem 0; }
    </style>
</head>
<body>
    <h1>Rental terms and conditions</h1>
    <p><strong>Booking reference:</strong> {{ $reference }}</p>
    @if (filled($customer))
        <p><strong>Customer:</strong> {{ $customer }}</p>
    @endif
    <div>{!! $terms !!}</div>
</body>
</html>
