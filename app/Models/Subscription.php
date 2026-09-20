<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use App\Enums\SubscriptionStatus;

class Subscription extends Pivot
{
    protected $guarded = ['id'];

    protected $casts = [
        'next_payment_due_at' => 'datetime',
        'failed_attempt_count' => 'integer',
        'status' => SubscriptionStatus::class,
    ];

    /**
     * Scope a query to only include subscriptions that can attempt payment.
     */
    public function scopeWherePaymentAttemptable($query) 
    {
        return $query
            ->whereIn('status', SubscriptionStatus::paymentAttemptable())
            ->where('next_payment_due_at', '<=', now()->endOfDay());
    }
}
