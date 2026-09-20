<?php

namespace App\Contracts;

use App\Models\Subscription;
use App\ValueObjects\PaymentResult;

interface PaymentGatewayInterface
{
    public function attempt(Subscription $subscription): PaymentResult;
}