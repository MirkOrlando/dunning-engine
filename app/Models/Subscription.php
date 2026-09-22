<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use App\Enums\SubscriptionStatus;
use App\Models\Payment;

class Subscription extends Pivot
{
    /** @use HasFactory<\Database\Factories\SubscriptionFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'next_payment_due_at' => 'datetime',
        'failed_attempt_count' => 'integer',
        'status' => SubscriptionStatus::class,
    ];

    /**
     * Scope a query to only include subscriptions that can attempt payment.
     */
    public function scopeWherePaymentAttemptable(Builder $query): Builder
    {
        return $query
            ->whereIn('status', SubscriptionStatus::paymentAttemptable())
            ->where('next_payment_due_at', '<=', now()->endOfDay());
    }

    /**
     * Get the payments for the subscription.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

}
