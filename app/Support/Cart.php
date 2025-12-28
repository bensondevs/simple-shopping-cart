<?php

namespace App\Support;

use App\Actions\Cart\AddToCart;
use App\Models\Product;
use App\Models\User;
use App\Models\UserProduct;
use Illuminate\Database\Eloquent\Collection;

class Cart
{
    protected ?Collection $items = null;

    public function __construct(protected User $user) {}

    public static function of(User $user): self
    {
        return new static($user);
    }

    public function items(): Collection
    {
        if ($this->items instanceof Collection) {
            return $this->items;
        }

        return $this->items = UserProduct::query()
            ->whereBelongsTo($this->user)
            ->with('product')
            ->get();
    }

    public function refresh(): void
    {
        $this->items = null;

        $this->items();
    }

    public function total(): float
    {
        return $this->items()->sum(fn (UserProduct $item) => $item->total);
    }

    public function count(): int
    {
        return $this->items()->count();
    }

    public function clear(): bool
    {
        return UserProduct::query()
            ->whereBelongsTo($this->user)
            ->delete();
    }

    public function has(Product $product): bool
    {
        return $this->items()
            ->where('product_id', $product->getKey())
            ->isNotEmpty();
    }

    public function quantity(Product $product): int
    {
        $item = $this->items()
            ->where('product_id', $product->getKey())
            ->first();

        return $item ? $item->quantity : 0;
    }

    public function add(Product $product, int $quantity): static
    {
        if ($product->stock_quantity < $quantity) {
            abort(422, 'Insufficient stock.');
        }

        $addedItem = app(AddToCart::class)(
            user: $this->user,
            product: $product,
            quantity: $quantity,
        );
        $this->items()->add($addedItem);

        return $this;
    }

    public function updateQuantity(UserProduct $item, int $quantity): void
    {
        if ($quantity <= 0) {
            $item->delete();

            $this->refresh();

            return;
        }

        if ($quantity > $item->product->stock_quantity) {
            $quantity = $item->product->stock_quantity;
        }

        $item->quantity = $quantity;
        $item->save();

        $this->refresh();
    }
}