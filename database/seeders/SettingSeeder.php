<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General Settings
            ['key' => 'platform_name', 'value' => 'ISP-MBP Enterprise', 'group' => 'general', 'type' => 'string'],
            ['key' => 'company_legal_name', 'value' => 'Broadband Networks Limited', 'group' => 'general', 'type' => 'string'],
            ['key' => 'support_email', 'value' => 'support@isp-mbp.ng', 'group' => 'general', 'type' => 'string'],
            ['key' => 'contact_phone', 'value' => '+234 800 000 0000', 'group' => 'general', 'type' => 'string'],
            ['key' => 'address', 'value' => 'Plot 102, Core Fibre Backbone Way, Abuja, Nigeria', 'group' => 'general', 'type' => 'string'],
            ['key' => 'currency_code', 'value' => 'NGN', 'group' => 'general', 'type' => 'string'],
            ['key' => 'currency_symbol', 'value' => '₦', 'group' => 'general', 'type' => 'string'],
            ['key' => 'default_tax_rate', 'value' => '7.5', 'group' => 'general', 'type' => 'float'],
            ['key' => 'invoice_due_days', 'value' => '7', 'group' => 'general', 'type' => 'integer'],

            // Email / SMTP Settings
            ['key' => 'mail_mailer', 'value' => 'smtp', 'group' => 'email', 'type' => 'string'],
            ['key' => 'mail_host', 'value' => 'smtp.mailtrap.io', 'group' => 'email', 'type' => 'string'],
            ['key' => 'mail_port', 'value' => '2525', 'group' => 'email', 'type' => 'integer'],
            ['key' => 'mail_username', 'value' => 'sandbox_user', 'group' => 'email', 'type' => 'string'],
            ['key' => 'mail_password', 'value' => 'sandbox_pass', 'group' => 'email', 'type' => 'string'],
            ['key' => 'mail_encryption', 'value' => 'tls', 'group' => 'email', 'type' => 'string'],
            ['key' => 'mail_from_address', 'value' => 'billing@isp-mbp.ng', 'group' => 'email', 'type' => 'string'],
            ['key' => 'mail_from_name', 'value' => 'ISP Billing Team', 'group' => 'email', 'type' => 'string'],

            // Payment Gateways
            ['key' => 'paystack_active', 'value' => '1', 'group' => 'payments', 'type' => 'boolean'],
            ['key' => 'paystack_mode_live', 'value' => '0', 'group' => 'payments', 'type' => 'boolean'],
            ['key' => 'paystack_public_key', 'value' => 'pk_test_sample_paystack_public_key', 'group' => 'payments', 'type' => 'string'],
            ['key' => 'paystack_secret_key', 'value' => 'sk_test_sample_paystack_secret_key', 'group' => 'payments', 'type' => 'string'],

            ['key' => 'monnify_active', 'value' => '0', 'group' => 'payments', 'type' => 'boolean'],
            ['key' => 'monnify_mode_live', 'value' => '0', 'group' => 'payments', 'type' => 'boolean'],
            ['key' => 'monnify_api_key', 'value' => 'MK_TEST_sample_key', 'group' => 'payments', 'type' => 'string'],
            ['key' => 'monnify_secret_key', 'value' => 'sample_secret_key', 'group' => 'payments', 'type' => 'string'],
            ['key' => 'monnify_contract_code', 'value' => '1234567890', 'group' => 'payments', 'type' => 'string'],

            ['key' => 'zainpay_active', 'value' => '0', 'group' => 'payments', 'type' => 'boolean'],
            ['key' => 'zainpay_mode_live', 'value' => '0', 'group' => 'payments', 'type' => 'boolean'],
            ['key' => 'zainpay_token', 'value' => 'sample_zainpay_token', 'group' => 'payments', 'type' => 'string'],
            ['key' => 'zainpay_zainbox_code', 'value' => 'sample_zainbox_code', 'group' => 'payments', 'type' => 'string'],

            // Notifications
            ['key' => 'notify_invoice_generated', 'value' => '1', 'group' => 'notifications', 'type' => 'boolean'],
            ['key' => 'notify_payment_received', 'value' => '1', 'group' => 'notifications', 'type' => 'boolean'],
            ['key' => 'notify_subscription_expiring', 'value' => '1', 'group' => 'notifications', 'type' => 'boolean'],
            ['key' => 'sms_provider', 'value' => 'termii', 'group' => 'notifications', 'type' => 'string'],
            ['key' => 'sms_api_key', 'value' => '', 'group' => 'notifications', 'type' => 'string'],
            ['key' => 'sms_sender_id', 'value' => 'ISPBILL', 'group' => 'notifications', 'type' => 'string'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
