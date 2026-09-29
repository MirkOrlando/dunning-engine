<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use App\Enums\SubscriptionStatus;
use App\Models\Payment;

class Subscription extends Model
{
    /** @use HasFactory<\Database\Factories\SubscriptionFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'next_payment_due_at' => 'datetime',
        'failed_attempts_count' => 'integer',
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

    /**
     * Get the customer that owns the subscription.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the plan that the subscription is for.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

}
