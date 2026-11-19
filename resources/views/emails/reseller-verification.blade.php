<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reseller Email Verification Code</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 30px;">
    <div style="max-width: 540px; margin: 0 auto; background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        <div style="background: #059669; padding: 24px; text-align: center; color: #ffffff;">
            <h2 style="margin: 0; font-size: 20px; font-weight: 700;">{{ $platformName }}</h2>
            <p style="margin: 4px 0 0; font-size: 13px; opacity: 0.9;">Authorized Voucher Reseller &amp; Agent Onboarding</p>
        </div>
        <div style="padding: 28px 24px; color: #334155; font-size: 14px; line-height: 1.6;">
            <p style="margin-top: 0;">Hello,</p>
            <p>Use the 6-digit verification code below to verify your email address (<strong>{{ $email }}</strong>) and complete your Authorized Voucher Reseller application:</p>

            <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 14px; padding: 20px; margin: 24px 0; text-align: center;">
                <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; color: #047857; font-weight: 700; margin-bottom: 6px;">Your Verification Code</div>
                <div style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 32px; font-weight: 800; letter-spacing: 0.22em; color: #065f46;">{{ $otp }}</div>
            </div>

            <p style="margin-bottom: 0; font-size: 12px; color: #64748b;">This code expires in <strong>15 minutes</strong>. If you did not initiate a reseller application, please ignore this email.</p>
        </div>
        <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 16px 24px; text-align: center; font-size: 11px; color: #94a3b8;">
            &copy; {{ date('Y') }} {{ $platformName }}. All rights reserved.
        </div>
    </div>
</body>
</html>
