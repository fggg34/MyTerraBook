@php
    $brandName = config('app.name', 'MyTerraBook');
    $accentColor = '#45a06a';
    $headingColor = '#0f2036';
    $unsubscribeUrl = rtrim(config('app.frontend_url', config('app.url')), '/').'/newsletter/unsubscribe?token='.$unsubscribeToken;
@endphp
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="color-scheme" content="light only">
    <meta name="supported-color-schemes" content="light only">
    <title>{{ $campaign->subject }}</title>
    <style>
        :root { color-scheme: light only; supported-color-schemes: light only; }
        html, body { margin:0 !important; padding:0 !important; width:100% !important; background-color:#eef2f8 !important; }
        img { border:0; line-height:100%; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic; }
        @media (prefers-color-scheme: dark) {
            body, .email-body { background-color:#eef2f8 !important; }
            .email-card, .email-card td { background-color:#ffffff !important; }
            .text-heading { color:{{ $headingColor }} !important; }
            .text-body { color:#1d2b40 !important; }
            .text-muted { color:#5a6b82 !important; }
        }
        [data-ogsc] .text-heading { color:{{ $headingColor }} !important; }
        [data-ogsc] .text-body { color:#1d2b40 !important; }
        [data-ogsc] .text-muted { color:#5a6b82 !important; }
        [data-ogsb] body, [data-ogsb] .email-body { background-color:#eef2f8 !important; }
        [data-ogsb] .email-card, [data-ogsb] .email-card td { background-color:#ffffff !important; }
    </style>
</head>
<body class="email-body" style="margin:0; padding:0; width:100%; background-color:#eef2f8;">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" class="email-body" bgcolor="#eef2f8" style="background-color:#eef2f8;">
        <tr>
            <td align="center" style="padding:24px 16px;">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="600" style="width:600px; max-width:600px;">
                    <tr>
                        <td align="left" style="padding:8px 8px 20px 8px;">
                            <span class="text-heading" style="font-family:'Quicksand','Open Sans',Arial,Helvetica,sans-serif; font-size:24px; font-weight:700; color:{{ $headingColor }};">My<span style="color:{{ $accentColor }};">Terra</span>Book</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="email-card" bgcolor="#ffffff" style="background-color:#ffffff; border:1px solid #e2e7ef; border-radius:18px; padding:32px 36px;">
                            <div class="text-body" style="font-family:'Open Sans',Arial,Helvetica,sans-serif; font-size:15px; line-height:1.65; color:#1d2b40;">
                                {!! $campaign->body !!}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:20px 16px 8px 16px;">
                            <p class="text-muted" style="font-family:'Open Sans',Arial,Helvetica,sans-serif; font-size:12px; line-height:1.6; color:#75839a; margin:0;">
                                You received this email because you subscribed to the {{ $brandName }} newsletter.<br>
                                <a href="{{ $unsubscribeUrl }}" style="color:{{ $accentColor }}; text-decoration:none;">Unsubscribe</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
