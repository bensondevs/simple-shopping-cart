<flux:modal 
    flyout 
    variant="floating" 
    name="cart-modal" 
    class="max-w-2xl flex flex-col"
    x-data="{}"
    @close-modal.window="
        const modalName = Array.isArray($event.detail) ? $event.detail[0] : $event.detail;
        if (modalName === 'cart-modal') {
            $dispatch('close-modal', 'cart-modal');
        }
    "
>
    <div class="mb-6">
        <flux:heading size="lg">{{ __('Shopping Cart') }}</flux:heading>
        <flux:subheading>{{ __('Review and manage your cart items') }}</flux:subheading>
    </div>

    <div class="flex-1 flex flex-col justify-between min-h-0">
        <div class="flex-1 overflow-y-auto min-h-0">
            @if ($this->items->isEmpty())
                <div class="p-8 text-center">
                    <flux:text class="text-zinc-600 dark:text-zinc-400">
                        {{ __('Your cart is empty') }}
                    </flux:text>
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($this->items as $item)
                        <div 
                            wire:key="item-{{ $item->getKey() }}"
                            class="flex flex-col items-start justify-between gap-4 p-4 border border-zinc-200 dark:border-zinc-700 rounded-lg"
                        >
                            <div class="flex flex-row justify-between w-full">
                                <div class="flex-1 min-w-0">
                                    <flux:text class="font-semibold truncate">{{ $item->product->name }}</flux:text>
                                    <flux:text class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">
                                        ${{ number_format($item->product->price, 2) }} × {{ $item->quantity }}
                                    </flux:text>
                                    <flux:text class="text-sm font-semibold text-accent mt-1">
                                        ${{ number_format($item->product->price * $item->quantity, 2) }}
                                    </flux:text>
                                    @if($item->quantity > $item->product->stock_quantity)
                                        <div class="mt-1 flex items-center gap-2">
                                            <flux:text class="text-sm text-red-600 dark:text-red-400">
                                                {{ __('Stock limit reached. Only :stock available.', ['stock' => $item->product->stock_quantity]) }}
                                            </flux:text>
                                            <flux:button
                                                variant="ghost"
                                                size="xs"
                                                wire:click="updateQuantityDirectly({{ $item->getKey() }}, {{ $item->product->stock_quantity }})"
                                                class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 h-auto py-0.5 px-2 text-xs"
                                            >
                                                {{ __('Reset to :stock', ['stock' => $item->product->stock_quantity]) }}
                                            </flux:button>
                                        </div>
                                    @endif
                                </div>

                                <flux:button
                                    variant="ghost"
                                    size="sm"
                                    wire:click="removeItem({{ $item->getKey() }})"
                                    class="h-8 w-8 p-0 text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                                >
                                    <flux:icon.x-mark class="size-4"/>
                                </flux:button>
                            </div>

                            <x-quantity-controls
                                :itemId="$item->getKey()"
                                :quantity="$item->quantity"
                                :maxQuantity="$item->product->stock_quantity"
                                size="sm"
                                wireIncrement="incrementQuantity({{ $item->getKey() }})"
                                wireDecrement="decrementQuantity({{ $item->getKey() }})"
                                wireUpdate="updateQuantityDirectly({{ $item->getKey() }}, $event.target.value)"
                            />
                        </div>
                    @endforeach

                    <div class="flex items-center justify-between gap-4 p-4 border border-zinc-200 dark:border-zinc-700 rounded-lg">
                        <flux:text class="text-lg font-semibold">
                            {{ __('Total') }}
                        </flux:text>
                        <flux:text class="text-2xl font-bold text-accent">
                            ${{ number_format($this->total, 2) }}
                        </flux:text>
                    </div>
                </div>
            @endif
        </div>

        <div class="flex-shrink-0 pt-4">
            <div class="flex flex-row gap-2 w-full">
                @if (!$this->items->isEmpty())
                    <flux:button
                        variant="primary"
                        wire:click="checkout"
                        wire:loading.attr="disabled"
                        class="flex-1"
                    >
                        <span wire:loading.remove wire:target="checkout">
                            {{ __('Checkout') }}
                        </span>
                        <span wire:loading wire:target="checkout">
                            {{ __('Processing...') }}
                        </span>
                    </flux:button>
                @endif
                <flux:modal.close class="flex-1 w-full">
                    <flux:button variant="filled" class="w-full">{{ __('Close') }}</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </div>
</flux:modal>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('close-modal', (data) => {
            const modalName = Array.isArray(data) ? data[0] : data;
            if (modalName === 'cart-modal') {
                window.dispatchEvent(new CustomEvent('close-modal', { detail: 'cart-modal' }));
            }
        });
    });
</script>

