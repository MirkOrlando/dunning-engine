<?php

namespace Database\Factories;

use App\Models\Subscription;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\SubscriptionStatus;
use Carbon\Carbon;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubscriptionStatus::Active,
            'next_payment_due_at' => fake()->dateTimeBetween('now', '+1 month'),
        ])->afterMaking(function (Subscription $subscription) {
            Payment::factory()->succeeded()->make([
                'attempted_at' => $subscription->next_payment_due_at->subMonth(),
            ])->recycle($subscription);
        })->afterCreating(function (Subscription $subscription) {
            Payment::factory()->succeeded()->create([
                'attempted_at' => $subscription->next_payment_due_at->subMonth(),
            ])->recycle($subscription);
        });

    }

    public function pastDue(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => SubscriptionStatus::PastDue,
                'next_payment_due_at' => fake()->dateTimeBetween('now', '+'.config('subscription.grace_period.days_interval', 3).' days'),
                'failed_attempt_count' => fake()->numberBetween(1, config('subscription.grace_period.attempts', 5) - 1),
            ];
        })->afterMaking(function (Subscription $subscription) {
            // Payment::factory()
        })->afterCreating(function (Subscription $subscription) {
            // ...
        });
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubscriptionStatus::Suspended,
            'next_payment_due_at' => null,
            'failed_attempt_count' => config('subscription.grace_period.attempts', 5),
        ]);
    }
}
