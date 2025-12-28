<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ __('Products') }}</flux:heading>
            <flux:subheading class="mt-1">{{ __('Browse and add products to your cart') }}</flux:subheading>
        </div>
    </div>

    @if (session('error'))
        <div class="rounded-xl border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-950 p-4">
            <flux:text class="text-red-800 dark:text-red-200">
                {{ session('error') }}
            </flux:text>
        </div>
    @endif

    @if($this->products->isEmpty())
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-12">
            <div class="text-center">
                <flux:text class="text-zinc-600 dark:text-zinc-400">
                    {{ __('No products available at the moment.') }}
                </flux:text>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach ($this->products as $product)
                <div class="flex flex-col rounded-xl border border-neutral-200 dark:border-neutral-700 overflow-hidden">
                    <div class="flex-1 p-6">
                        <flux:heading size="lg" class="mb-2">{{ $product->name }}</flux:heading>

                        <div class="mb-4">
                            <flux:text class="text-2xl font-bold text-accent">
                                ${{ number_format($product->price, 2) }}
                            </flux:text>
                        </div>

                        <div class="mb-4">
                            <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                @if($product->stock_quantity > 0)
                                    {{ __('In Stock') }}: {{ $product->stock_quantity }} {{ __('items') }}
                                @else
                                    <span class="text-red-600 dark:text-red-400">{{ __('Out of Stock') }}</span>
                                @endif
                            </flux:text>
                        </div>
                    </div>

                    <x-product-cart-controls 
                        :product="$product" 
                        :cartQuantity="$this->cart()->quantity($product)"
                    />
                </div>
            @endforeach
        </div>
    @endif
</div>
