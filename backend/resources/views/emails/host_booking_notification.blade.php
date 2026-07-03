@php
    $currency = $d['currency'] ?? 'USD';
    $fmt = fn ($amount) => $currency.' '.number_format((float) $amount, 2);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Booking</title>
</head>
<body style="margin:0;padding:0;background:#f4f5f7;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <div style="max-width:600px;margin:0 auto;padding:24px;">
        <div style="background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb;">
            <div style="background:#1d4ed8;padding:24px 28px;color:#ffffff;">
                <h1 style="margin:0;font-size:20px;">New Booking — Guest Arriving with Cash Balance</h1>
                <p style="margin:6px 0 0;font-size:14px;opacity:.9;">Booking reference: <strong>{{ $d['booking_reference'] }}</strong></p>
            </div>

            <div style="padding:28px;">
                <p style="margin:0 0 16px;font-size:15px;">Hi {{ $d['host_name'] }},</p>
                <p style="margin:0 0 20px;font-size:15px;line-height:1.6;">
                    You have a new booking for <strong>{{ $d['listing_name'] }}</strong>
                    @if(!empty($d['check_in']) && !empty($d['check_out']))
                        ({{ $d['check_in'] }} &rarr; {{ $d['check_out'] }})
                    @endif.
                    The platform fee has been collected online. You will collect the remaining balance in cash on arrival.
                </p>

                <table role="presentation" width="100%" style="border-collapse:collapse;border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;">
                    <tr style="background:#f9fafb;">
                        <td colspan="2" style="padding:12px 16px;font-size:13px;font-weight:bold;letter-spacing:.5px;color:#6b7280;">PAYMENT SUMMARY</td>
                    </tr>
                    <tr>
                        <td style="padding:12px 16px;font-size:15px;border-top:1px solid #eef0f2;">Total Booking Value</td>
                        <td style="padding:12px 16px;font-size:15px;text-align:right;border-top:1px solid #eef0f2;">{{ $fmt($d['total_price']) }}</td>
                    </tr>
                    <tr>
                        <td style="padding:12px 16px;font-size:15px;border-top:1px solid #eef0f2;color:#047857;">✅ Platform Fee Collected (online)</td>
                        <td style="padding:12px 16px;font-size:15px;text-align:right;border-top:1px solid #eef0f2;color:#047857;font-weight:bold;">{{ $fmt($d['platform_fee']) }}</td>
                    </tr>
                    <tr style="background:#fffbeb;">
                        <td style="padding:14px 16px;font-size:15px;border-top:1px solid #eef0f2;color:#b45309;font-weight:bold;">💵 You Collect on Arrival (Cash)</td>
                        <td style="padding:14px 16px;font-size:16px;text-align:right;border-top:1px solid #eef0f2;color:#b45309;font-weight:bold;">{{ $fmt($d['cash_due_on_arrival']) }}</td>
                    </tr>
                </table>

                <div style="margin-top:20px;padding:16px 18px;background:#fef3c7;border:1px solid #fcd34d;border-radius:10px;">
                    <p style="margin:0;font-size:15px;font-weight:bold;color:#92400e;">
                        Please collect {{ $fmt($d['cash_due_on_arrival']) }} in cash from {{ $d['guest_name'] }} when they arrive.
                    </p>
                </div>

                <div style="margin-top:24px;font-size:14px;color:#374151;line-height:1.6;">
                    <strong>Guest contact</strong><br>
                    {{ $d['guest_name'] }}
                    @if(!empty($d['guest_email']))<br>{{ $d['guest_email'] }}@endif
                    @if(!empty($d['guest_phone']))<br>{{ $d['guest_phone'] }}@endif
                </div>
            </div>
        </div>
        <p style="text-align:center;color:#9ca3af;font-size:12px;margin-top:16px;">MyTerraBook</p>
    </div>
</body>
</html>
