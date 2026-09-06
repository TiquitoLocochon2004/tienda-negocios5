<?php

namespace App\Services;

use App\Contracts\PaymentGateway;

class LocalPaymentGateway implements PaymentGateway
{
    public function charge(float $amount, string $paymentMethod): string
    {
        return 'local-' . bin2hex(random_bytes(8));
    }
}
