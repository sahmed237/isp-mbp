<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Reseller;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResellerApplicationStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Reseller $reseller,
        public string $decision
    ) {}

    public function envelope(): Envelope
    {
        $platformName = (string) (Setting::get('platform_name') ?: 'ISP-MBP');
        $fromAddress = (string) (Setting::get('mail_from_address') ?: config('mail.from.address', 'no-reply@isp-mbp.local'));
        $fromName = (string) (Setting::get('mail_from_name') ?: config('mail.from.name', $platformName));

        $subject = $this->decision === 'approved'
            ? "Reseller Application Approved ({$this->reseller->reseller_code}) - {$platformName}"
            : "Update on Your Reseller Application ({$this->reseller->reseller_code}) - {$platformName}";

        return new Envelope(
            from: new Address($fromAddress, $fromName),
            subject: $subject,
        );
    }

    public function content(): Content
    {
        $companyInfo = Setting::getCompanyInfo();

        return new Content(
            view: 'emails.reseller-application-status',
            with: [
                'platformName' => (string) (Setting::get('platform_name') ?: $companyInfo['name']),
                'supportEmail' => $companyInfo['email'],
                'reseller' => $this->reseller,
                'decision' => $this->decision,
                'loginUrl' => route('reseller.login'),
            ]
        );
    }
}
