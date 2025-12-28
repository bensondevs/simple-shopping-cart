<?php

namespace App\Actions\Cart;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\User;
use App\Support\Cart;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Checkout
{
    public function __invoke(User $user): Order
    {
        $cart = Cart::of($user);
        $items = $cart->items();

        if ($items->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => 'Cart is empty.',
            ]);
        }

        return DB::transaction(function () use ($user, $cart, $items) {
            $order = new Order();
            $order->user()->associate($user);
            $order->status = OrderStatus::Pending;
            $order->reference = 'ORD-' . now()->format('YmdHis');
            $order->total = 0;
            $order->save();

            foreach ($items as $item) {
                $quantity = min($item->quantity, $item->product->stock_quantity);
                
                $orderProduct = new OrderProduct();
                $orderProduct->order()->associate($order);
                $orderProduct->product()->associate($item->product);
                $orderProduct->name = $item->product->name;
                $orderProduct->quantity = $quantity;
                $orderProduct->price = $item->product->price;
                $orderProduct->save();

                $order->total += $item->product->price * $quantity;

                // Update product stock (observer will handle low stock event)
                $item->product->decrement('stock_quantity', $quantity);
            }

            $order->save();

            $cart->clear();

            return $order;
        });
    }
}