<?php

declare(strict_types=1);

namespace App\Services\Payment\DTOs;

class PaymentVerifyResult
{
    public function __construct(
        public float $amountPaid,
        public string $paymentDate,
        public string $gateway,
        public ?string $rawResponse = null,
        public ?string $reference = null,
        public float $fee = 0.0
    ) {}
}
