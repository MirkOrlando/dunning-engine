<?php

namespace App\Enums;

enum PaymentFailureReason: string
{
    case InsufficientFunds = 'insufficient_funds';
    case CardExpired = 'card_expired';
    case CardDeclined = 'card_declined';

    public static function all(): array
    {
        return [
            self::InsufficientFunds,
            self::CardExpired,
            self::CardDeclined,
        ];
    }
}