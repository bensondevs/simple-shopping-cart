<?php

namespace App\Livewire;

use App\Livewire\Concerns\ManagesCart;
use App\Models\Product;
use App\Support\Cart;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ListProducts extends Component
{
    use ManagesCart;

    public function getListeners(): array
    {
        return ['cart-updated' => '$refresh'];
    }

    #[Computed]
    public function products(): Collection
    {
        return Product::query()->latest()->get();
    }

    #[Computed]
    public function cart(): Cart
    {
        return once(fn (): Cart => Cart::of(Auth::user()));
    }

    public function addToCart(Product $product, int $quantity = 1): void
    {
        $this->cart()->add($product, $quantity);

        $this->dispatch('cart-updated');
    }

}
