<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reseller Application Status Update</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 30px;">
    <div style="max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        @if($decision === 'approved')
            <div style="background: #059669; padding: 26px 24px; text-align: center; color: #ffffff;">
                <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.1em; opacity: 0.9; font-weight: 700; margin-bottom: 4px;">Application Approved</div>
                <h2 style="margin: 0; font-size: 21px; font-weight: 800;">Welcome to {{ $platformName }}</h2>
                <p style="margin: 6px 0 0; font-size: 13px; opacity: 0.92;">Authorized Voucher Reseller &amp; Partner Network</p>
            </div>

            <div style="padding: 28px 24px; color: #334155; font-size: 14px; line-height: 1.6;">
                <p style="margin-top: 0;">Dear <strong>{{ $reseller->contact_person }}</strong>,</p>
                <p>Congratulations! Your application for <strong>{{ $reseller->business_name }}</strong> has been reviewed and <strong>approved</strong> by our operations team. Your reseller portal account is now active.</p>

                <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 14px; padding: 18px 20px; margin: 22px 0; font-size: 13px; color: #065f46;">
                    <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; color: #047857; font-weight: 700; margin-bottom: 10px;">Your Agent Account Details</div>
                    <div style="margin-bottom: 6px;"><strong>Reseller Code:</strong> <span style="font-family: monospace; font-weight: 800; font-size: 14px;">{{ $reseller->reseller_code }}</span></div>
                    <div style="margin-bottom: 6px;"><strong>Business Name:</strong> {{ $reseller->business_name }}</div>
                    <div style="margin-bottom: 6px;"><strong>Login Email:</strong> {{ $reseller->email }}</div>
                    <div><strong>Status:</strong> <span style="color: #059669; font-weight: 700;">Active &amp; Verified</span></div>
                </div>

                <p>You can now sign in to your Reseller Portal using your <strong>Reseller Code ({{ $reseller->reseller_code }})</strong> or <strong>Email ({{ $reseller->email }})</strong> and the password you created during registration to fund your wallet and generate Hotspot Voucher batches.</p>

                <div style="text-align: center; margin: 28px 0 18px;">
                    <a href="{{ $loginUrl }}" style="display: inline-block; background: #059669; color: #ffffff; text-decoration: none; font-weight: 700; font-size: 14px; padding: 13px 28px; border-radius: 12px;">
                        Sign In to Reseller Portal &rarr;
                    </a>
                </div>
            </div>
        @else
            <div style="background: #e11d48; padding: 26px 24px; text-align: center; color: #ffffff;">
                <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.1em; opacity: 0.9; font-weight: 700; margin-bottom: 4px;">Application Update</div>
                <h2 style="margin: 0; font-size: 21px; font-weight: 800;">{{ $platformName }}</h2>
                <p style="margin: 6px 0 0; font-size: 13px; opacity: 0.92;">Voucher Reseller Application Review</p>
            </div>

            <div style="padding: 28px 24px; color: #334155; font-size: 14px; line-height: 1.6;">
                <p style="margin-top: 0;">Dear <strong>{{ $reseller->contact_person }}</strong>,</p>
                <p>Thank you for applying to become an Authorized Voucher Reseller for <strong>{{ $reseller->business_name }}</strong> (Reference: <strong>{{ $reseller->reseller_code }}</strong>).</p>
                <p>After reviewing your application and KYC submission, we are unable to approve your reseller account at this time for the following reason:</p>

                <div style="background: #fff1f2; border: 1px solid #fecdd3; border-radius: 14px; padding: 18px 20px; margin: 22px 0; font-size: 13px; color: #881337;">
                    <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; color: #be123c; font-weight: 700; margin-bottom: 6px;">Review Feedback / Reason</div>
                    <div style="font-weight: 600;">{{ $reseller->rejection_reason ?? 'KYC documentation or business verification requirements were not met.' }}</div>
                </div>

                <p style="margin-bottom: 0;">If you have questions or wish to provide updated KYC documentation, please contact our support team at <strong>{{ $supportEmail }}</strong>.</p>
            </div>
        @endif

        <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 16px 24px; text-align: center; font-size: 11px; color: #94a3b8;">
            &copy; {{ date('Y') }} {{ $platformName }}. All rights reserved.
        </div>
    </div>
</body>
</html>
