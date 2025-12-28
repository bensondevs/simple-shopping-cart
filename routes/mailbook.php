<?php

use App\Mail\MailbookMail;
use App\Models\OrderProduct;
use App\Models\User;
use App\Notifications\DailySalesReportNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Xammie\Mailbook\Facades\Mailbook;

// Daily Sales Report Notification Preview
Mailbook::add(function () {
    $today = Carbon::today();
    $tomorrow = Carbon::tomorrow();

    // Get all order products from orders created today
    $orderProducts = OrderProduct::query()
        ->whereHas('order', fn (Builder $query) => $query->whereBetween('created_at', [$today, $tomorrow]))
        ->with(['product', 'order'])
        ->get();

    // Group by product and calculate totals
    $salesData = $orderProducts
        ->groupBy('product_id')
        ->map(fn (Collection $items) => [
            'product_id' => $items->first()?->product_id,
            'product_name' => $items->first()->name,
            'total_quantity' => $items->sum('quantity'),
            'total_revenue' => $items->sum(fn ($item) => $item->getRawOriginal('price') * $item->quantity),
            'orders_count' => $items->unique('order_id')->count(),
        ])
        ->values();

    $totalRevenue = $orderProducts->sum(fn ($item) => $item->getRawOriginal('price') * $item->quantity);
    $totalItemsSold = $orderProducts->sum('quantity');
    $totalOrders = $orderProducts->unique('order_id')->count();

    return new DailySalesReportNotification(
        $salesData,
        $totalRevenue,
        $totalItemsSold,
        $totalOrders,
        $today
    );
})
    ->label('Daily Sales Report')
    ->to(new User(['name' => 'Admin User', 'email' => 'admin@example.com']));
