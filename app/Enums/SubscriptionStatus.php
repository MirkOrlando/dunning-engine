<?php

namespace App\Enums;

enum SubscriptionStatus: string
{
    case Active = 'active';
    case PastDue = 'past_due';
    case Suspended = 'suspended';

    /**
     * Statuses in which a payment attempt can be made.
     */
    public function canAttemptPayment(): bool
    {
        return in_array($this, [self::Active, self::PastDue], true);
    }

    /**
     * Static helper if you need the list without an instance.
     */
    public static function paymentAttemptable(): array
    {
        return [self::Active, self::PastDue];
    }
}