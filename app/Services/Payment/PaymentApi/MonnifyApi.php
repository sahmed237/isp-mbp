<?php

declare(strict_types=1);

namespace App\Services\Payment\PaymentApi;

use App\Models\Setting;
use Exception;
use Illuminate\Support\Facades\Http;

class MonnifyApi
{
    private static ?self $instance = null;
    private string $apiKey;
    private string $secretKey;
    private string $baseUrl;
    private ?string $token = null;

    private function __construct()
    {
        $this->apiKey = (string) (Setting::get('monnify_api_key') ?: config('services.monnify.api_key', ''));
        $this->secretKey = (string) (Setting::get('monnify_secret_key') ?: config('services.monnify.secret_key', ''));
        $isLive = (bool) Setting::get('monnify_mode_live', false);
        $this->baseUrl = $isLive ? 'https://api.monnify.com' : 'https://sandbox.monnify.com';
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

    private function login(): string
    {
        if ($this->token) {
            return $this->token;
        }

        if (empty($this->apiKey) || empty($this->secretKey)) {
            throw new Exception('Monnify API Key or Secret Key is not configured in Settings.');
        }

        $response = $this->httpClient()->withHeaders([
            'Authorization' => 'Basic ' . base64_encode($this->apiKey . ':' . $this->secretKey),
        ])->post($this->baseUrl . '/api/v1/auth/login');

        if (!$response->successful()) {
            throw new Exception('Failed to authenticate with Monnify API: ' . $response->body());
        }

        $body = $response->json();
        $this->token = $body['responseBody']['accessToken'] ?? null;

        if (!$this->token) {
            throw new Exception('Monnify authentication succeeded but no access token was returned.');
        }

        return $this->token;
    }

    public function post(string $endpoint, array $body = []): array
    {
        $token = $this->login();

        $response = $this->httpClient()->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->post($this->baseUrl . '/' . ltrim($endpoint, '/'), $body);

        if (!$response->successful()) {
            throw new Exception('Monnify API error: ' . $response->body());
        }

        return $response->json();
    }

    public function get(string $endpoint, array $query = []): array
    {
        $token = $this->login();

        $response = $this->httpClient()->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->get($this->baseUrl . '/' . ltrim($endpoint, '/'), $query);

        if (!$response->successful()) {
            throw new Exception('Monnify API error: ' . $response->body());
        }

        return $response->json();
    }
}
