@props([
    'itemId' => null,
    'quantity' => 0,
    'maxQuantity' => 999,
    'size' => 'md', // 'md' or 'sm'
    'wireIncrement' => null,
    'wireDecrement' => null,
    'wireUpdate' => null,
])

<div class="flex items-center gap-2 w-full justify-between">
    <flux:button
        variant="filled"
        wire:click="{{ $wireDecrement ?? 'decrementQuantity(' . $itemId . ')' }}"
        class="flex-shrink-0"
        wire:loading.attr="disabled"
    >
        -
    </flux:button>

    <input
        min="0"
        max="{{ $maxQuantity }}"
        value="{{ $quantity }}"
        wire:change="{{ $wireUpdate ?? 'updateQuantityDirectly(' . $itemId . ', $event.target.value)' }}"
        class="flex-1 font-semibold text-center rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 p-2"
        wire:loading.attr="disabled"
    />

    <flux:button
        variant="primary"
        wire:click="{{ $wireIncrement ?? 'incrementQuantity(' . $itemId . ')' }}"
        class="flex-shrink-0"
        wire:loading.attr="disabled"
        :disabled="$quantity >= $maxQuantity"
    >
        +
    </flux:button>
</div>

