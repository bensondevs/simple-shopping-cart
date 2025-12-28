<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendLowStockEmailJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Product $product
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $adminUser = User::where('email', 'admin@example.com')->first();

        if ($adminUser) {
            $adminUser->notify(new LowStockNotification($this->product));
        }
    }
}
