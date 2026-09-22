<?php

namespace Database\Factories;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\PaymentFailureReason;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'attempted_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'failure_reason' => fake()->randomElement(PaymentFailureReason::all()),
        ];
    }

    public function succeeded(): static
    {
        return $this->state(fn (array $attributes) => [
            'succeeded' => true
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'succeeded' => false,
            'failure_reason' => fake()->randomElement(PaymentFailureReason::all()),
        ]);
    }
}
