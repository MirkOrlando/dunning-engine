<?php

namespace Database\Factories;

use App\Models\Subscription;
use App\Models\Payment;
use App\Models\Customer;
use App\Models\Plan;
use App\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\Sequence;

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
        return [
            'customer_id' => Customer::factory(),
            'plan_id' => Plan::factory(),
            'status'=> SubscriptionStatus::Active,
            'next_payment_due_at' => now()->addDays(fake()->numberBetween(1, 30)),
            'failed_attempt_count' => 0,
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'status' => SubscriptionStatus::Active,
            'next_payment_due_at' => now()->addDays(fake()->numberBetween(1, 30)),
            'failed_attempt_count' => 0,
        ])->afterCreating(function (Subscription $subscription) {
            Payment::factory()->succeeded()->for($subscription)->create([
                'attempted_at' => $subscription->next_payment_due_at->copy()->subMonth(),
            ]);
        });
    }

    public function pastDue(?int $failedAttempts = null): static
    {
        $interval = config('subscription.grace_period.days_interval', 3);
        $maxAttempts = config('subscription.grace_period.attempts', 5);

        return $this->state(fn () => [
            'status' => SubscriptionStatus::PastDue,
            'next_payment_due_at' => now()->addDays(fake()->numberBetween(0, $interval)),
            'failed_attempt_count' => $failedAttempts ?? fake()->numberBetween(1, $maxAttempts - 1),
        ])->afterCreating(
            fn (Subscription $subscription) => $this->createFailedPayments($subscription, $subscription->failed_attempt_count, $interval)
        );
    }

    public function suspended(): static
    {
        $interval = config('subscription.grace_period.days_interval', 3);
        $maxAttempts = config('subscription.grace_period.attempts', 5);

        return $this->state(fn () => [
            'status' => SubscriptionStatus::Suspended,
            'next_payment_due_at' => null,
            'failed_attempt_count' => $maxAttempts,
        ])->afterCreating(
            fn (Subscription $subscription) => $this->createFailedPayments($subscription, $maxAttempts, $interval, from: now())
        );
    }

    private function createFailedPayments(Subscription $subscription, int $count, int $interval, $from = null): void
    {
        $anchor = ($from ?? $subscription->next_payment_due_at)->copy();

        Payment::factory()
            ->failed()
            ->for($subscription)
            ->count($count)
            ->sequence(fn (Sequence $sequence) => [
                'attempted_at' => $anchor->copy()->subDays($interval * ($sequence->index + 1)),
            ])
            ->create();
    }

    public function pastDueAfterRecovery(int $failedAttempts = 2): static
    {
        $interval = config('subscription.grace_period.days_interval', 3);

        return $this->pastDue($failedAttempts)->afterCreating(function (Subscription $subscription) use ($interval, $failedAttempts) {
            $beforeStreak = $subscription->next_payment_due_at->copy()->subDays($interval * ($failedAttempts + 1));

            Payment::factory()->succeeded()->for($subscription)->create(['attempted_at' => $beforeStreak]);
            Payment::factory()->failed()->for($subscription)->create(['attempted_at' => $beforeStreak->copy()->subDays($interval)]);
        });
    }
}
