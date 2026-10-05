<?php

declare(strict_types=1);

namespace App\Services\Payment\Gateways;

use App\Services\Payment\Contracts\PaymentGatewayInterface;
use Exception;

class PaymentGatewayFactory
{
    public static function create(string $gateway): PaymentGatewayInterface
    {
        return match (strtolower($gateway)) {
            'paystack' => new PaystackGateway(),
            'monnify'  => new MonnifyGateway(),
            default    => throw new Exception("Unsupported payment gateway: {$gateway}"),
        };
    }
}
