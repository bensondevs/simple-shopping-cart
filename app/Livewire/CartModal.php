<?php

namespace App\Livewire;

use App\Livewire\Concerns\ManagesCart;
use App\Models\UserProduct;
use App\Support\Cart;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;
use Livewire\Component;

class CartModal extends Component
{
    use ManagesCart;

    protected $listeners = ['cart-updated' => '$refresh'];

    #[Computed]
    public function cart(): Cart
    {
        return once(fn (): Cart => Cart::of(Auth::user()));
    }

    #[Computed]
    public function items(): Collection
    {
        return $this->cart()->items();
    }

    #[Computed]
    public function total(): float
    {
        return $this->cart()->total();
    }

    #[Computed]
    public function count(): int
    {
        return $this->cart()->count();
    }

    #[On('update-item-quantity')]
    #[Renderless]
    public function updateQuantity(int $item, int $quantity): void
    {
        $userProduct = UserProduct::find($item);

        if (! $userProduct || $userProduct->user_id !== Auth::id()) {
            return;
        }

        if ($quantity <= 0) {
            $userProduct->delete();
            $this->cart()->refresh();
            $this->dispatch('cart-updated');
            return;
        }

        Cart::of(auth()->user())->updateQuantity($userProduct, $quantity);

        $this->dispatch('cart-updated');
    }

    #[On('remove-item')]
    #[Renderless]
    public function removeItem(int $item): void
    {
        $userProduct = UserProduct::find($item);

        if (! $userProduct || $userProduct->user_id !== Auth::id()) {
            return;
        }

        $userProduct->delete();
        
        $this->cart()->refresh();

        $this->dispatch('cart-updated');
    }
}

