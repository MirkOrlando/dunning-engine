<?php

namespace App\Jobs;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Contracts\PaymentGatewayInterface;

class ProcessSubscriptionsPaymentJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
        public function __construct(
            private readonly PaymentGatewayInterface $gateway,
        ) {}


    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // get the subscriptions with status across all users that are due for payment today or before today
        // process the payment and save the result in the payments table
        // update the subscription:
            // if payment is successful, set status as active, reset failed_attempt_count and set the next_payment_due_at
            // else if the payment fails
                // if failed_attempt_count is less than 5, set the subscription status to past_due, increment the failed_attempt_count and set the next_payment_due_at to 3 days later
                // else set the subscription status to suspended and increment the failed_attempt_count 
        
        Subscription::wherePaymentAttemptable()
            ->chunk(100, function ($subscriptions) {
                foreach ($subscriptions as $subscription) {
                    // process the payment
                    $paymentResult = $this->gateway->attempt($subscription);

                    // create a payment record
                    $payment = $subscription->payments()->create([
                        'attempted_at' => now(),
                        'succeeded' => $paymentResult->succeeded,
                        'failure_reason' => optional($paymentResult->failureReason)->value ?? null,
                    ]);

                    if ($payment->succeeded) {
                        $subscription->update([
                            'status' => SubscriptionStatus::Active,
                            'failed_attempt_count' => 0,
                            'next_payment_due_at' => now()->addMonth(),
                        ]);
                    } else {
                        if ($subscription->failed_attempt_count < config('subscription.grace_period.attempts', 5)) {
                            $subscription->update([
                                'status' => SubscriptionStatus::PastDue,
                                'failed_attempt_count' => $subscription->failed_attempt_count++,
                                'next_payment_due_at' => now()->addDays(config('subscription.grace_period.days_interval', 3)),
                            ]);
                        } else {
                            $subscription->update([
                                'status' => SubscriptionStatus::Suspended,
                                'failed_attempt_count' => $subscription->failed_attempt_count++,
                                'next_payment_due_at' => null,
                            ]);
                        }
                    }
                }
            });
    }
}
