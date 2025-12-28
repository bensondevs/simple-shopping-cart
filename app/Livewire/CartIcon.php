<?php

namespace App\Livewire;

use App\Support\Cart;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class CartIcon extends Component
{
    protected $listeners = ['cart-updated' => '$refresh'];

    #[Computed]
    public function cart(): Cart
    {
        return once(fn (): Cart => Cart::of(Auth::user()));
    }

    #[Computed]
    public function count(): int
    {
        return $this->cart()->count();
    }

    public function render()
    {
        return view('livewire.cart-icon');
    }
}

