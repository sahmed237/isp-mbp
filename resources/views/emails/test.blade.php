<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>SMTP Test</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 30px;">
    <div style="max-width: 540px; margin: 0 auto; background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        <div style="background: #2563eb; padding: 24px; text-align: center; color: #ffffff;">
            <h2 style="margin: 0; font-size: 20px; font-weight: 700;">{{ $platformName }}</h2>
            <p style="margin: 4px 0 0; font-size: 13px; opacity: 0.85;">SMTP Email Diagnostic Test</p>
        </div>
        <div style="padding: 24px; color: #334155; font-size: 14px; line-height: 1.6;">
            <p style="margin-top: 0;">Hello,</p>
            <p>This is a test notification verifying that your ISP management platform's SMTP mail transport configuration is functioning properly.</p>
            
            <div style="background: #f1f5f9; border-radius: 12px; padding: 16px; margin: 20px 0; font-family: monospace; font-size: 12px;">
                <div><strong>SMTP Host:</strong> {{ $host }}</div>
                <div><strong>SMTP Port:</strong> {{ $port }}</div>
                <div><strong>Timestamp:</strong> {{ $sentAt }}</div>
                <div><strong>Status:</strong> <span style="color: #16a34a; font-weight: bold;">Connected & Delivered</span></div>
            </div>

            <p style="margin-bottom: 0; font-size: 12px; color: #64748b;">If you received this message, your mail credentials and server connectivity are configured correctly.</p>
        </div>
        <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 16px 24px; text-align: center; font-size: 11px; color: #94a3b8;">
            &copy; {{ date('Y') }} {{ $platformName }}. All rights reserved.
        </div>
    </div>
</body>
</html>
