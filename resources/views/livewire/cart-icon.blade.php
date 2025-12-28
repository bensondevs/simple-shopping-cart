<flux:modal.trigger name="cart-modal">
    <button
        type="button"
        class="relative flex items-center justify-center rounded-lg p-2 text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800"
    >
        <flux:icon.shopping-bag class="size-6"/>

        @if ($this->count > 0)
            <span class="absolute -top-1 -end-1 flex h-5 w-5 items-center justify-center rounded-full bg-accent text-xs font-semibold text-accent-foreground">
                {{ $this->count }}
            </span>
        @endif
    </button>
</flux:modal.trigger>

