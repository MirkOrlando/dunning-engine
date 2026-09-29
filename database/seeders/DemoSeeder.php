<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subscription;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i=0; $i < 53; $i++) { 
            Subscription::factory()->active()->create();
        }

        for ($i=0; $i < 12; $i++) { 
            Subscription::factory()->pastDue()->create();
        }

        for ($i=0; $i < 5; $i++) {
            $failedAttemps = random_int(1, config('subscription.grace_period.attempts') - 1);
            Subscription::factory()->pastDueAfterRecovery($failedAttemps)->create();
        }

        for ($i=0; $i < 8; $i++) { 
            Subscription::factory()->suspended()->create();
        }
    }
}
