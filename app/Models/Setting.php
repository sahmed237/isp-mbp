<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
    ];

    /**
     * Helper to get a setting value with automatic type casting
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        if (!$setting) {
            return $default;
        }

        $value = $setting->value;

        return match ($setting->type) {
            'boolean' => (bool) $value,
            'integer' => (int) $value,
            'float', 'numeric' => (float) $value,
            default => $value,
        };
    }

    /**
     * Helper to set or create a setting value without corrupting existing group/type
     */
    public static function set(string $key, mixed $value, ?string $group = null, ?string $type = null): self
    {
        $setting = static::where('key', $key)->first();
        $strValue = is_bool($value) ? ($value ? '1' : '0') : (string) $value;

        if ($setting) {
            $updateData = ['value' => $strValue];
            if ($group !== null) {
                $updateData['group'] = $group;
            }
            if ($type !== null) {
                $updateData['type'] = $type;
            }
            $setting->update($updateData);
            return $setting;
        }

        // Infer group and type for new settings
        $inferredGroup = $group ?? (
            str_starts_with($key, 'paystack_') || str_starts_with($key, 'monnify_') || str_starts_with($key, 'zainpay_')
                ? 'payments'
                : (str_starts_with($key, 'mail_') ? 'email' : (str_starts_with($key, 'notify_') || str_starts_with($key, 'sms_') ? 'notifications' : 'general'))
        );

        $inferredType = $type ?? (
            is_bool($value) || str_ends_with($key, '_active') || str_ends_with($key, '_live')
                ? 'boolean'
                : 'string'
        );

        return static::create([
            'key' => $key,
            'value' => $strValue,
            'group' => $inferredGroup,
            'type' => $inferredType,
        ]);
    }

    /**
     * Get company/ISP identity details from settings with sensible fallbacks
     *
     * @return array{name: string, address: string, phone: string, email: string, bank_name: string, bank_account_name: string, bank_account_number: string}
     */
    public static function getCompanyInfo(): array
    {
        return [
            'name' => (string) (static::get('company_legal_name') ?: static::get('platform_name') ?: 'Broadband Networks Limited'),
            'address' => (string) (static::get('address') ?: static::get('company_address') ?: 'Plot 102, Core Fibre Backbone Way, Abuja, Nigeria'),
            'phone' => (string) (static::get('contact_phone') ?: static::get('company_phone') ?: '+234 800 000 0000'),
            'email' => (string) (static::get('support_email') ?: static::get('company_email') ?: static::get('mail_from_address') ?: 'support@isp-mbp.ng'),
            'bank_name' => (string) (static::get('bank_name') ?: 'Zenith Bank PLC'),
            'bank_account_name' => (string) (static::get('bank_account_name') ?: static::get('company_legal_name') ?: static::get('platform_name') ?: 'Broadband Networks Limited'),
            'bank_account_number' => (string) (static::get('bank_account_number') ?: '1012345678'),
        ];
    }

    /**
     * Apply email settings to Laravel config dynamically at runtime
     */
    public static function configureMailer(): void
    {
        $emailSettings = static::where('group', 'email')->pluck('value', 'key');

        if ($emailSettings->isEmpty()) {
            return;
        }

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.transport' => 'smtp',
            'mail.mailers.smtp.host' => $emailSettings['mail_host'] ?? config('mail.mailers.smtp.host'),
            'mail.mailers.smtp.port' => (int) ($emailSettings['mail_port'] ?? config('mail.mailers.smtp.port', 587)),
            'mail.mailers.smtp.encryption' => $emailSettings['mail_encryption'] ?? config('mail.mailers.smtp.encryption', 'tls'),
            'mail.mailers.smtp.username' => $emailSettings['mail_username'] ?? config('mail.mailers.smtp.username'),
            'mail.mailers.smtp.password' => $emailSettings['mail_password'] ?? config('mail.mailers.smtp.password'),
            'mail.from.address' => $emailSettings['mail_from_address'] ?? config('mail.from.address'),
            'mail.from.name' => $emailSettings['mail_from_name'] ?? config('mail.from.name'),
        ]);
    }

    /**
     * Get all settings as a key-value array
     */
    public static function getAllSettings(): array
    {
        return static::all()->pluck('value', 'key')->toArray();
    }
}
