@php
    $brandName = $brandName ?? config('app.name', 'MyTerraBook');
    $accentColor = $accentColor ?? '#45a06a';
    $headingColor = $headingColor ?? '#0f2036';
    $textColor = '#1d2b40';
    $mutedColor = '#5a6b82';
    $cardBg = '#ffffff';
    $pageBg = '#eef2f8';
    $borderColor = '#e2e7ef';
    $logoMode = $logoMode ?? 'text';
    $logoUrl = $logoUrl ?? '';
    $preheader = $preheader ?? '';
    $heading = $heading ?? '';
    $greeting = $greeting ?? '';
    $bodyHtml = $bodyHtml ?? '';
    $ctaLabel = $ctaLabel ?? '';
    $ctaUrl = $ctaUrl ?? '';
    $footerNote = $footerNote ?? '';
    $footerText = $footerText ?? '';
    $supportEmail = $supportEmail ?? '';
    $companyAddress = $companyAddress ?? '';
    $year = $year ?? date('Y');
@endphp
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    {{-- Force light rendering so dark-mode clients (Gmail/Outlook/Apple Mail) do not invert the white background --}}
    <meta name="color-scheme" content="light only">
    <meta name="supported-color-schemes" content="light only">
    <title>{{ $brandName }}</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    <style>
        :root {
            color-scheme: light only;
            supported-color-schemes: light only;
        }

        html, body {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            background-color: #eef2f8 !important;
        }

        body, table, td, a {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        img {
            border: 0;
            line-height: 100%;
            outline: none;
            text-decoration: none;
            -ms-interpolation-mode: bicubic;
        }

        /* Apple Mail / iOS dark mode: re-assert the light palette so backgrounds stay white. */
        @media (prefers-color-scheme: dark) {
            body, .email-body { background-color: #eef2f8 !important; }
            .email-card { background-color: #ffffff !important; }
            .email-card td { background-color: #ffffff !important; }
            .text-heading { color: {{ $headingColor }} !important; }
            .text-body { color: #1d2b40 !important; }
            .text-muted { color: #5a6b82 !important; }
            .cta-button a { color: #ffffff !important; }
        }

        /* Outlook.com (Windows) dark mode overrides text colours via [data-ogsc] and backgrounds via [data-ogsb]. */
        [data-ogsc] .text-heading { color: {{ $headingColor }} !important; }
        [data-ogsc] .text-body { color: #1d2b40 !important; }
        [data-ogsc] .text-muted { color: #5a6b82 !important; }
        [data-ogsc] .cta-button a { color: #ffffff !important; }
        [data-ogsb] body, [data-ogsb] .email-body { background-color: #eef2f8 !important; }
        [data-ogsb] .email-card, [data-ogsb] .email-card td { background-color: #ffffff !important; }
    </style>
</head>
<body class="email-body" style="margin:0; padding:0; width:100%; background-color:#eef2f8;">
    {{-- Preheader: hidden preview text --}}
    <div style="display:none; max-height:0; overflow:hidden; mso-hide:all; font-size:1px; line-height:1px; color:#eef2f8; opacity:0;">
        {{ $preheader }}&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;
    </div>

    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" class="email-body" bgcolor="#eef2f8" style="background-color:#eef2f8;">
        <tr>
            <td align="center" style="padding:24px 16px;">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="600" style="width:600px; max-width:600px;">
                    {{-- Header / logo --}}
                    <tr>
                        <td align="left" style="padding:8px 8px 20px 8px;">
                            @if ($logoMode === 'image' && $logoUrl !== '')
                                <img src="{{ $logoUrl }}" alt="{{ $brandName }}" height="36" style="height:36px; display:block;">
                            @else
                                <span style="font-family:'Quicksand','Open Sans',Arial,Helvetica,sans-serif; font-size:24px; font-weight:700; color:{{ $headingColor }};" class="text-heading">My<span style="color:{{ $accentColor }};">Terra</span>Book</span>
                            @endif
                        </td>
                    </tr>

                    {{-- Card --}}
                    <tr>
                        <td class="email-card" bgcolor="#ffffff" style="background-color:#ffffff; border:1px solid #e2e7ef; border-radius:18px; padding:36px 36px 32px 36px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                @if ($heading !== '')
                                    <tr>
                                        <td class="text-heading" style="font-family:'Quicksand','Open Sans',Arial,Helvetica,sans-serif; font-size:22px; font-weight:700; line-height:1.3; color:{{ $headingColor }}; padding-bottom:16px;">
                                            {{ $heading }}
                                        </td>
                                    </tr>
                                @endif
                                @if ($greeting !== '')
                                    <tr>
                                        <td class="text-body" style="font-family:'Open Sans',Arial,Helvetica,sans-serif; font-size:16px; line-height:1.6; color:#1d2b40; padding-bottom:8px;">
                                            {{ $greeting }}
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <td class="text-body" style="font-family:'Open Sans',Arial,Helvetica,sans-serif; font-size:15px; line-height:1.65; color:#1d2b40;">
                                        {!! $bodyHtml !!}
                                    </td>
                                </tr>

                                @if ($ctaLabel !== '' && $ctaUrl !== '')
                                    <tr>
                                        <td align="left" style="padding:24px 0 8px 0;">
                                            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                                <tr>
                                                    <td class="cta-button" align="center" bgcolor="{{ $accentColor }}" style="background-color:{{ $accentColor }}; border-radius:999px;">
                                                        <!--[if mso]>
                                                        <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $ctaUrl }}" style="height:48px;v-text-anchor:middle;width:240px;" arcsize="50%" stroke="f" fillcolor="{{ $accentColor }}">
                                                            <w:anchorlock/>
                                                            <center style="color:#ffffff;font-family:Arial,sans-serif;font-size:15px;font-weight:bold;">{{ $ctaLabel }}</center>
                                                        </v:roundrect>
                                                        <![endif]-->
                                                        <!--[if !mso]><!-->
                                                        <a href="{{ $ctaUrl }}" target="_blank" style="display:inline-block; padding:14px 30px; font-family:'Open Sans',Arial,Helvetica,sans-serif; font-size:15px; font-weight:700; color:#ffffff; text-decoration:none; border-radius:999px;">{{ $ctaLabel }}</a>
                                                        <!--<![endif]-->
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                @endif

                                @if ($footerNote !== '')
                                    <tr>
                                        <td class="text-muted" style="font-family:'Open Sans',Arial,Helvetica,sans-serif; font-size:13px; line-height:1.6; color:#5a6b82; padding-top:24px; border-top:1px solid #e2e7ef; margin-top:16px;">
                                            {{ $footerNote }}
                                        </td>
                                    </tr>
                                @endif
                            </table>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td align="center" style="padding:24px 16px 8px 16px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                @if ($footerText !== '')
                                    <tr>
                                        <td class="text-muted" align="center" style="font-family:'Open Sans',Arial,Helvetica,sans-serif; font-size:12px; line-height:1.6; color:#75839a; padding-bottom:6px;">
                                            {{ $footerText }}
                                        </td>
                                    </tr>
                                @endif
                                @if ($supportEmail !== '')
                                    <tr>
                                        <td class="text-muted" align="center" style="font-family:'Open Sans',Arial,Helvetica,sans-serif; font-size:12px; line-height:1.6; color:#75839a; padding-bottom:6px;">
                                            Need help? <a href="mailto:{{ $supportEmail }}" style="color:{{ $accentColor }}; text-decoration:none;">{{ $supportEmail }}</a>
                                        </td>
                                    </tr>
                                @endif
                                @if ($companyAddress !== '')
                                    <tr>
                                        <td class="text-muted" align="center" style="font-family:'Open Sans',Arial,Helvetica,sans-serif; font-size:12px; line-height:1.6; color:#8090a4; padding-bottom:6px;">
                                            {{ $companyAddress }}
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <td class="text-muted" align="center" style="font-family:'Open Sans',Arial,Helvetica,sans-serif; font-size:12px; line-height:1.6; color:#8090a4;">
                                        &copy; {{ $year }} {{ $brandName }}. All rights reserved.
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
