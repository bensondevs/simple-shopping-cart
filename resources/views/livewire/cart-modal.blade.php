<flux:modal flyout variant="floating" name="cart-modal" class="max-w-2xl flex flex-col">
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
                <div class="space-y-4" 
                     x-data="{
                         total: {{ $this->total }},
                         formatPrice(amount) {
                             return (amount / 100).toFixed(2);
                         },
                         calculateTotal() {
                             let sum = 0;
                             const items = this.$el.querySelectorAll('[data-item-total]');
                             items.forEach(itemEl => {
                                 const itemData = itemEl.__x?.$data;
                                 if (itemData) {
                                     if (!itemData.removed && itemEl.style.display !== 'none') {
                                         sum += (itemData.quantity || 0) * (itemData.price || 0);
                                     }
                                 } else {
                                     // Fallback to data attributes if Alpine not initialized yet
                                     const quantity = parseFloat(itemEl.getAttribute('data-quantity') || 0);
                                     const price = parseFloat(itemEl.getAttribute('data-price') || 0);
                                     if (itemEl.style.display !== 'none') {
                                         sum += quantity * price;
                                     }
                                 }
                             });
                             this.total = sum;
                             return sum;
                         }
                     }"
                     x-init="
                         const updateTotal = () => {
                             setTimeout(() => calculateTotal(), 10);
                         };
                         updateTotal();
                         
                         // Watch for quantity changes in items
                         const watchItems = () => {
                             this.$el.querySelectorAll('[data-item-total]').forEach(itemEl => {
                                 const itemData = itemEl.__x?.$data;
                                 if (itemData && !itemEl._totalWatcher) {
                                     itemEl._totalWatcher = true;
                                     this.$watch(() => itemData.quantity, () => updateTotal());
                                 }
                             });
                         };
                         
                         setTimeout(watchItems, 100);
                         $watch('$el.querySelectorAll(\'[data-item-total]\').length', () => {
                             setTimeout(watchItems, 100);
                             updateTotal();
                         });
                     "
                     @quantity-changed.window="calculateTotal()"
                     @remove-item.window="setTimeout(() => calculateTotal(), 150)">
                    @foreach ($this->items as $item)
                        <div 
                            :wire:key="$item->getKey()" 
                            data-item-total
                            data-quantity="{{ $item->quantity }}"
                            data-price="{{ $item->product->price }}"
                            x-data="{ 
                                quantity: {{ $item->quantity }}, 
                                price: {{ $item->product->price }},
                                removed: false,
                                formatPrice(amount) {
                                    return (amount / 100).toFixed(2);
                                },
                                updateQuantity(newQuantity) {
                                    if (newQuantity <= 0) {
                                        this.remove();
                                        return;
                                    }
                                    this.quantity = Math.max(1, Math.min(newQuantity, {{ $item->product->stock_quantity }}));
                                    this.$el.setAttribute('data-quantity', this.quantity);
                                    $dispatch('quantity-changed');
                                    $dispatch('update-item-quantity', { item: {{ $item->getKey() }}, quantity: this.quantity });
                                },
                                increment() {
                                    if (this.quantity < {{ $item->product->stock_quantity }}) {
                                        this.updateQuantity(this.quantity + 1);
                                    }
                                },
                                decrement() {
                                    if (this.quantity <= 1) {
                                        this.remove();
                                    } else {
                                        this.updateQuantity(this.quantity - 1);
                                    }
                                },
                                remove() {
                                    this.removed = true;
                                    $dispatch('remove-item', { item: {{ $item->getKey() }} });
                                }
                            }"
                            x-show="!removed"
                            x-transition
                            class="flex flex-col items-start justify-between gap-4 p-4 border border-zinc-200 dark:border-zinc-700 rounded-lg"
                        >
                            <div class="flex flex-row justify-between w-full">
                                <div class="flex-1 min-w-0">
                                    <flux:text class="font-semibold truncate">{{ $item->product->name }}</flux:text>
                                    <flux:text class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">
                                        $<span x-text="formatPrice(price)"></span> × <span x-text="quantity"></span>
                                    </flux:text>
                                    <flux:text class="text-sm font-semibold text-accent mt-1">
                                        $<span x-text="formatPrice(quantity * price)"></span>
                                    </flux:text>
                                </div>

                                <flux:button
                                    variant="ghost"
                                    size="sm"
                                    x-on:click="remove()"
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

                    <div
                        class="flex items-center justify-between gap-4 p-4 border border-zinc-200 dark:border-zinc-700 rounded-lg">
                        <flux:text class="text-lg font-semibold">
                            {{ __('Total') }}
                        </flux:text>
                        <flux:text class="text-2xl font-bold text-accent">
                            $<span x-text="formatPrice(total)"></span>
                        </flux:text>
                    </div>
                </div>
            @endif
        </div>

        <div class="flex-shrink-0 pt-4">
            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="filled">{{ __('Close') }}</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </div>
</flux:modal>

