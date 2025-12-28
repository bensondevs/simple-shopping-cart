<?php

namespace App\Listeners;

use App\Events\ProductStockLow;
use App\Jobs\SendLowStockEmailJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendLowStockNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(ProductStockLow $event): void
    {
        SendLowStockEmailJob::dispatch($event->product);
    }
}
