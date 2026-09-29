<?php

namespace App\ValueObjects;

use App\Enums\PaymentFailureReason;

final readonly class PaymentResult
{
    private function __construct(
        public bool $succeeded,
        public ?PaymentFailureReason $failureReason = null,
    ) {}

    public static function success(): self
    {
        return new self(succeeded: true);
    }

    public static function failure(PaymentFailureReason $reason): self
    {
        return new self(succeeded: false, failureReason: $reason);
    }
}