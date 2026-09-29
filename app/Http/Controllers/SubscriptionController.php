<?php

namespace App\Http\Controllers;

use App\Contracts\PaymentGatewayInterface;
use App\Jobs\ProcessSubscriptionsPaymentJob;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionController extends Controller
{
    /**
     * Display the customers' subscriptions with their status and payment history.
     */
    public function index(): Response
    {
        $subscriptions = Subscription::with([
            'customer',
            'plan',
            'payments' => fn ($query) => $query->orderByDesc('attempted_at'),
        ])
            ->orderBy('next_payment_due_at')
            ->get();

        return Inertia::render('Subscriptions/Index', [
            'subscriptions' => $subscriptions,
        ]);
    }

    /**
     * Force-run the job that processes subscription payments.
     */
    public function processPayments(): RedirectResponse
    {
        dispatch_sync(new ProcessSubscriptionsPaymentJob(app(PaymentGatewayInterface::class)));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Payment processing job executed.')]);

        return to_route('subscriptions.index');
    }
}
