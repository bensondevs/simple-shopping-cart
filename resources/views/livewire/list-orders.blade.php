<div class="space-y-6">
    <div>
        <flux:heading size="lg">{{ __('My Orders') }}</flux:heading>
        <flux:subheading>{{ __('View and manage your order history') }}</flux:subheading>
    </div>

    @if ($this->orders->isEmpty())
        <div class="p-8 text-center border border-zinc-200 dark:border-zinc-700 rounded-lg">
            <flux:text class="text-zinc-600 dark:text-zinc-400">
                {{ __('You have no orders yet') }}
            </flux:text>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($this->orders as $order)
                <div 
                    wire:key="order-{{ $order->getKey() }}"
                    class="border border-zinc-200 dark:border-zinc-700 rounded-lg p-6 hover:bg-zinc-50 dark:hover:bg-zinc-900/50 transition-colors"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-4">
                                <flux:text class="font-semibold text-lg">
                                    {{ __('Order') }} #{{ $order->reference }}
                                </flux:text>
                                <flux:badge 
                                    :variant="$order->status->badgeVariant()"
                                >
                                    {{ ucfirst($order->status->value) }}
                                </flux:badge>
                            </div>
                            <flux:text class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">
                                {{ $order->created_at->format('M d, Y h:i A') }}
                            </flux:text>
                            <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                {{ $order->orderProducts->count() }} {{ __('item(s)') }}
                            </flux:text>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="text-right">
                                <flux:text class="text-xl font-bold text-accent">
                                    ${{ number_format($order->total, 2) }}
                                </flux:text>
                            </div>
                            <div class="flex items-center gap-2">
                                @if ($order->status === \App\Enums\OrderStatus::Pending)
                                    <flux:button 
                                        variant="primary" 
                                        size="sm"
                                        wire:click="payOrder({{ $order->id }})"
                                        wire:loading.attr="disabled"
                                    >
                                        <span wire:loading.remove wire:target="payOrder({{ $order->id }})">
                                            {{ __('Pay') }}
                                        </span>
                                        <span wire:loading wire:target="payOrder({{ $order->id }})">
                                            {{ __('Processing...') }}
                                        </span>
                                    </flux:button>
                                @endif
                                <flux:button 
                                    variant="ghost" 
                                    size="sm"
                                    wire:click="showOrderDetails({{ $order->id }})"
                                >
                                    {{ __('View Details') }}
                                </flux:button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
            </div>
        @endif

    @if (!$this->orders->isEmpty())
        <div class="mt-6">
            {{ $this->orders->links() }}
        </div>
    @endif

    <flux:modal
        name="order-details"
        @close="$wire.closeOrderDetails()"
        flyout
        variant="floating"
    >
        @if ($selectedOrder)
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('Order Details') }}</flux:heading>
                    <flux:subheading>{{ __('Order') }} #{{ $selectedOrder->reference }}</flux:subheading>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 border border-zinc-200 dark:border-zinc-700 rounded-lg">
                        <flux:text class="font-semibold">{{ __('Status') }}</flux:text>
                        <flux:badge 
                            :variant="$selectedOrder->status->badgeVariant()"
                        >
                            {{ ucfirst($selectedOrder->status->value) }}
                        </flux:badge>
                    </div>

                    <div class="flex items-center justify-between p-4 border border-zinc-200 dark:border-zinc-700 rounded-lg">
                        <flux:text class="font-semibold">{{ __('Order Date') }}</flux:text>
                        <flux:text>{{ $selectedOrder->created_at->format('M d, Y h:i A') }}</flux:text>
                    </div>

                    <div class="space-y-2">
                        <flux:text class="font-semibold">{{ __('Items') }}</flux:text>
                        <div class="space-y-2">
                            @foreach ($selectedOrder->orderProducts as $orderProduct)
                                <div class="flex items-center justify-between p-4 border border-zinc-200 dark:border-zinc-700 rounded-lg">
                                    <div class="flex-1">
                                        <flux:text class="font-medium">{{ $orderProduct->name }}</flux:text>
                                        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                            ${{ number_format($orderProduct->price, 2) }} × {{ $orderProduct->quantity }}
                                        </flux:text>
                                    </div>
                                    <flux:text class="font-semibold">
                                        ${{ number_format($orderProduct->price * $orderProduct->quantity, 2) }}
                                    </flux:text>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex items-center justify-between p-4 border border-zinc-200 dark:border-zinc-700 rounded-lg bg-zinc-50 dark:bg-zinc-900">
                        <flux:text class="text-lg font-semibold">{{ __('Total') }}</flux:text>
                        <flux:text class="text-2xl font-bold text-accent">
                            ${{ number_format($selectedOrder->total, 2) }}
                        </flux:text>
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    @if ($selectedOrder->status === \App\Enums\OrderStatus::Pending)
                        <flux:button 
                            variant="primary"
                            wire:click="payOrder({{ $selectedOrder->id }})"
                            wire:loading.attr="disabled"
                        >
                            <span wire:loading.remove wire:target="payOrder({{ $selectedOrder->id }})">
                                {{ __('Pay') }}
                            </span>
                            <span wire:loading wire:target="payOrder({{ $selectedOrder->id }})">
                                {{ __('Processing...') }}
                            </span>
                        </flux:button>
                    @endif
                    <flux:modal.close>
                        <flux:button variant="filled" wire:click="closeOrderDetails">
                            {{ __('Close') }}
                        </flux:button>
                    </flux:modal.close>
                </div>
            </div>
        @endif
    </flux:modal>
</div>
