<?php

namespace App\Jobs;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\DB;

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
                        DB::transaction(function () use ($subscription, $paymentResult) {
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
                            $newCount = $subscription->failed_attempt_count + 1;
                            $maxAttempts = config('subscription.grace_period.attempts', 5);

                            if ($newCount < $maxAttempts) {
                                $subscription->increment('failed_attempt_count', 1, [
                                    'status' => SubscriptionStatus::PastDue,
                                    'next_payment_due_at' => now()->addDays(config('subscription.grace_period.days_interval', 3)),
                                ]);
                            } else {
                                $subscription->increment('failed_attempt_count', 1, [
                                    'status' => SubscriptionStatus::Suspended,
                                    'next_payment_due_at' => null,
                                ]);
                            }
                        }
                    });
                }
            });
    }
}
