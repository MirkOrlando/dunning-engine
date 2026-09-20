<?php

namespace App\ValueObjects;

final readonly class PaymentResult
{
    private function __construct(
        public bool $succeeded,
        public ?string $failureReason = null,
    ) {}

    public static function success(): self
    {
        return new self(succeeded: true);
    }

    public static function failure(string $reason): self
    {
        return new self(succeeded: false, failureReason: $reason);
    }
}