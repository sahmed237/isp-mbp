<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function envelope(): Envelope
    {
        $fromAddress = Setting::get('mail_from_address', config('mail.from.address'));
        $fromName = Setting::get('mail_from_name', config('mail.from.name', 'ISP Platform'));

        return new Envelope(
            subject: 'SMTP Connection Test - ' . Setting::get('platform_name', 'ISP-MBP'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.test',
            with: [
                'platformName' => Setting::get('platform_name', 'ISP Management & Billing Platform'),
                'sentAt' => now()->format('Y-m-d H:i:s T'),
                'host' => Setting::get('mail_host', config('mail.mailers.smtp.host')),
                'port' => Setting::get('mail_port', config('mail.mailers.smtp.port')),
            ]
        );
    }
}
