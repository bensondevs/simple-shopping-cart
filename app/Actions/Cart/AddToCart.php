<?php

namespace App\Actions\Cart;

use App\Models\Product;
use App\Models\User;
use App\Models\UserProduct;
use Illuminate\Validation\ValidationException;

class AddToCart
{
    public function __invoke(User $user, Product $product, int $quantity = 1): UserProduct
    {
        $cartItem = UserProduct::query()
            ->whereBelongsTo($user, 'user')
            ->whereBelongsTo($product, 'product')
            ->firstOrNew();
        $cartItem->user()->associate($user);
        $cartItem->product()->associate($product);
        $cartItem->quantity = ($cartItem->quantity ?? 0) + $quantity;
        $cartItem->save();

        return $cartItem;
    }
}

