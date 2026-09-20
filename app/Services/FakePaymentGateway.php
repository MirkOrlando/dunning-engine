<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Subscription;
use App\Enums\PaymentFailureReason;
use App\ValueObjects\PaymentResult;

class FakePaymentGateway implements PaymentGatewayInterface
{
    public function attempt(Subscription $subscription): PaymentResult
    {
        // logica randomica 80/20 che usi in demo
        return random_int(1, 100) <= 80
            ? PaymentResult::success()
            : PaymentResult::failure(PaymentFailureReason::all()[random_int(0, 2)]);
    }
}