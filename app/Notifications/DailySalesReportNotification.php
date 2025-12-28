<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DailySalesReportNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Collection $salesData,
        public int $totalRevenue,
        public int $totalItemsSold,
        public int $totalOrders,
        public Carbon $date
    ) {
        //
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Daily Sales Report - ' . $this->date->format('F j, Y'))
            ->line('Here is your daily sales report for ' . $this->date->format('F j, Y') . '.')
            ->line('**Total Orders:** ' . $this->totalOrders)
            ->line('**Total Items Sold:** ' . $this->totalItemsSold)
            ->line('**Total Revenue:** $' . number_format($this->totalRevenue / 100, 2));

        if ($this->salesData->isNotEmpty()) {
            $message->line('## Products Sold Today:');
            
            foreach ($this->salesData as $item) {
                $message->line(sprintf(
                    '- **%s**: %d units sold, $%s revenue (%d orders)',
                    $item['product_name'],
                    $item['total_quantity'],
                    number_format($item['total_revenue'] / 100, 2),
                    $item['orders_count']
                ));
            }
        } else {
            $message->line('No sales were recorded today.');
        }

        $message->action('View Orders', url('/orders'));

        return $message;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'date' => $this->date->toDateString(),
            'total_orders' => $this->totalOrders,
            'total_items_sold' => $this->totalItemsSold,
            'total_revenue' => $this->totalRevenue,
            'sales_data' => $this->salesData->toArray(),
        ];
    }
}
