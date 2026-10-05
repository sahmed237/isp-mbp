<?php

declare(strict_types=1);

namespace App\Services\Payment\PaymentApi;

use App\Models\Setting;
use Exception;
use Illuminate\Support\Facades\Http;

class PaystackApi
{
    private static ?self $instance = null;
    private string $secretKey;
    private string $baseUrl;

    private function __construct()
    {
        $this->secretKey = (string) (Setting::get('paystack_secret_key') ?: config('services.paystack.secret_key', ''));
        $this->baseUrl = 'https://api.paystack.co';
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function httpClient()
    {
        $verify = !app()->isLocal() && !app()->runningUnitTests() && !empty(ini_get('curl.cainfo'));
        return $verify ? Http::asJson() : Http::withoutVerifying()->asJson();
    }

    public function post(string $endpoint, array $body = []): array
    {
        if (empty($this->secretKey)) {
            throw new Exception('Paystack Secret Key is not configured in System Settings.');
        }

        $response = $this->httpClient()->withHeaders([
            'Authorization' => 'Bearer ' . $this->secretKey,
            'Accept' => 'application/json',
        ])->post($this->baseUrl . '/' . ltrim($endpoint, '/'), $body);

        if (!$response->successful()) {
            $this->handleError($response);
        }

        return $response->json();
    }

    public function get(string $endpoint, array $query = []): array
    {
        if (empty($this->secretKey)) {
            throw new Exception('Paystack Secret Key is not configured in System Settings.');
        }

        $response = $this->httpClient()->withHeaders([
            'Authorization' => 'Bearer ' . $this->secretKey,
            'Accept' => 'application/json',
        ])->get($this->baseUrl . '/' . ltrim($endpoint, '/'), $query);

        if (!$response->successful()) {
            $this->handleError($response);
        }

        return $response->json();
    }

    private function handleError($response): void
    {
        $data = $response->json();
        $message = $data['message'] ?? 'Paystack API request failed';
        throw new Exception('Paystack API error: ' . $message);
    }
}
