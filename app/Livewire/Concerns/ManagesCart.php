<?php

namespace App\Livewire\Concerns;

use App\Models\Product;
use App\Models\UserProduct;
use App\Support\Cart;
use Illuminate\Support\Facades\Auth;

trait ManagesCart
{
    protected function findUserProduct(int $itemId): ?UserProduct
    {
        return UserProduct::query()
            ->whereBelongsTo(Auth::user(), 'user')
            ->find($itemId);
    }

    protected function findCartItem(Product $product): ?UserProduct
    {
        return UserProduct::query()
            ->whereBelongsTo(Auth::user(), 'user')
            ->whereBelongsTo($product, 'product')
            ->first();
    }

    protected function refreshCart(): void
    {
        $this->cart()->refresh();
        $this->dispatch('cart-updated');
    }

    public function incrementQuantity(int $item): void
    {
        $userProduct = $this->findUserProduct($item);

        if (! $userProduct instanceof UserProduct) {
            return;
        }

        if ($userProduct->quantity >= $userProduct->product->stock_quantity) {
            return;
        }

        Cart::of(auth()->user())->updateQuantity($userProduct, $userProduct->quantity + 1);

        $this->dispatch('cart-updated');
    }

    public function decrementQuantity(int $item): void
    {
        $userProduct = $this->findUserProduct($item);

        if (! $userProduct instanceof UserProduct) {
            return;
        }

        if ($userProduct->quantity <= 1) {
            $userProduct->delete();

            return;
        }

        Cart::of(auth()->user())->updateQuantity($userProduct, $userProduct->quantity - 1);

        $this->refreshCart();
    }

    public function updateQuantityDirectly(int $item, $value): void
    {
        $quantity = (int) $value;
        $userProduct = $this->findUserProduct($item);

        if (! $userProduct instanceof UserProduct) {
            return;
        }

        if ($quantity <= 0) {
            $userProduct->delete();
            $this->refreshCart();
            return;
        }

        Cart::of(auth()->user())->updateQuantity($userProduct, $quantity);
        $this->dispatch('cart-updated');
    }

    public function incrementProductQuantity(Product $product): void
    {
        $currentQuantity = $this->cart()->quantity($product);
        
        if ($product->stock_quantity <= $currentQuantity) {
            session()->flash('error', __('Cannot add more items. Stock limit reached.'));
            return;
        }
        
        $this->addToCart($product, 1);
    }

    public function decrementProductQuantity(Product $product): void
    {
        $cartItem = $this->findCartItem($product);

        if (! $cartItem instanceof UserProduct) {
            return;
        }

        if ($cartItem->quantity > 1) {
            $cartItem->decrement('quantity');
        } else {
            $cartItem->delete();
        }

        $this->dispatch('cart-updated');
    }

    public function updateProductQuantityDirectly(Product $product, $value): void
    {
        $quantity = (int) $value;
        
        if ($quantity <= 0) {
            $this->decrementProductQuantity($product);

            return;
        }

        $cartItem = $this->findCartItem($product);

        if (! $cartItem instanceof UserProduct) {
            $this->addToCart($product, $quantity);

            return;
        }

        Cart::of(auth()->user())->updateQuantity($cartItem, $quantity);

        $this->dispatch('cart-updated');
    }
}

