<?php

namespace App\Livewire;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class ListOrders extends Component
{
    use WithPagination;

    public ?Order $selectedOrder = null;

    public function mount(?int $order = null): void
    {
        if ($order !== null) {
            $this->showOrderDetails($order);
        }
    }

    #[Computed]
    public function orders(): LengthAwarePaginator
    {
        return Order::query()
            ->where('user_id', Auth::id())
            ->with('orderProducts')
            ->latest()
            ->paginate(10);
    }

    public function showOrderDetails(int $orderId): void
    {
        $this->selectedOrder = Order::query()
            ->where('id', $orderId)
            ->where('user_id', Auth::id())
            ->with('orderProducts')
            ->first();
        
        $this->modal('order-details')->show();
    }

    public function closeOrderDetails(): void
    {
        $this->selectedOrder = null;
        $this->modal('order-details')->close();
    }

    public function payOrder(int $orderId): void
    {
        $order = Order::query()
            ->where('id', $orderId)
            ->where('user_id', Auth::id())
            ->first();

        if ($order && $order->status === OrderStatus::Pending) {
            $order->status = OrderStatus::Paid;
            $order->save();

            // Refresh the selected order if it's the one being paid
            if ($this->selectedOrder && $this->selectedOrder->id === $orderId) {
                $this->selectedOrder->refresh();
                $this->selectedOrder->load('orderProducts');
            }
        }
    }
}
