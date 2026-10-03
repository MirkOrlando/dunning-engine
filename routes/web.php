<?php

use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

// Guests are bounced to the login page by the auth middleware on the subscriptions page.
Route::redirect('/', '/subscriptions')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::post('subscriptions/process-payments', [SubscriptionController::class, 'processPayments'])->name('subscriptions.process-payments');
});

require __DIR__.'/settings.php';
