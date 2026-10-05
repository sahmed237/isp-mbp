<?php

declare(strict_types=1);

namespace App\Services\Payment\DTOs;

class PaymentInitResult
{
    public function __construct(
        public string $reference,
        public string $redirectUrl,
        public ?string $rawResponse = null
    ) {}
}
