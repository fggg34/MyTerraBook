<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $campaign->subject }}</title>
</head>
<body style="font-family: system-ui, -apple-system, sans-serif; line-height: 1.6; color: #1a1a1a; max-width: 640px; margin: 0 auto; padding: 24px;">
    <div style="margin-bottom: 32px;">
        {!! $campaign->body !!}
    </div>
    <hr style="border: none; border-top: 1px solid #e5e5e5; margin: 32px 0;">
    <p style="font-size: 13px; color: #666;">
        You received this email because you subscribed to the MyTerraBook newsletter.
        <a href="{{ rtrim(config('app.frontend_url', config('app.url')), '/') }}/newsletter/unsubscribe?token={{ $unsubscribeToken }}" style="color: #666;">
            Unsubscribe
        </a>
    </p>
</body>
</html>
