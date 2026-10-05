<?php

declare(strict_types=1);

namespace App\Services\Payment\Contracts;

use App\Services\Payment\DTOs\PaymentInitResult;
use App\Services\Payment\DTOs\PaymentVerifyResult;

interface PaymentGatewayInterface
{
    public function initialize(mixed $invoice, string $email, string $phone, string $callbackUrl, ?string $reference = null): PaymentInitResult;

    public function verify(string $reference): PaymentVerifyResult;

    public function webhook(array $payload, array $headers, string $rawBody): ?PaymentVerifyResult;
}
