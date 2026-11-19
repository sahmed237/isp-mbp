<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResellerEmailVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $otp,
        public string $email
    ) {}

    public function envelope(): Envelope
    {
        $platformName = (string) (Setting::get('platform_name') ?: 'ISP-MBP');
        $fromAddress = (string) (Setting::get('mail_from_address') ?: config('mail.from.address', 'no-reply@isp-mbp.local'));
        $fromName = (string) (Setting::get('mail_from_name') ?: config('mail.from.name', $platformName));

        return new Envelope(
            from: new Address($fromAddress, $fromName),
            subject: "Your Reseller Verification Code ({$this->otp}) - {$platformName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reseller-verification',
            with: [
                'platformName' => Setting::get('platform_name', 'ISP Management & Billing Platform'),
                'otp' => $this->otp,
                'email' => $this->email,
            ]
        );
    }
}
