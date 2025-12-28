@props([
    'product',
    'cartQuantity' => 0,
])

@php
    $isInCart = $cartQuantity > 0;
@endphp

<div class="p-1 border-t border-neutral-200 dark:border-neutral-700">
    @if ($product->stock_quantity > 0)
        @if ($isInCart)
            <div class="space-y-2">
                <x-quantity-controls
                    :itemId="$product->getKey()"
                    :quantity="$cartQuantity"
                    :max-quantity="$product->stock_quantity"
                    wireIncrement="incrementProductQuantity({{ $product->getKey() }})"
                    wireDecrement="decrementProductQuantity({{ $product->getKey() }})"
                    wireUpdate="updateProductQuantityDirectly({{ $product->getKey() }}, $event.target.value)"
                />

                @if($cartQuantity > $product->stock_quantity)
                    <flux:text class="text-sm text-red-600 dark:text-red-400 text-center block">
                        {{ __('Stock limit reached') }}
                    </flux:text>
                @endif
            </div>
        @else
            <flux:button
                variant="primary"
                wire:click="addToCart({{ $product->getKey() }}, 1)"
                class="w-full"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove wire:target="addToCart({{ $product->getKey() }}, 1)">
                    {{ __('Add to Cart') }}
                </span>
                <span wire:loading wire:target="addToCart({{ $product->getKey() }}, 1)">
                    {{ __('Adding...') }}
                </span>
            </flux:button>
        @endif
    @else
        <flux:button
            variant="ghost"
            disabled
            class="w-full"
        >
            {{ __('Out of Stock') }}
        </flux:button>
    @endif
</div>

