<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Enums\OrderStatus;
use App\Models\Contracts\HasCurrency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Squire\Models\Currency;

class Order extends Model implements HasCurrency
{
    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'total' => MoneyCast::class,
        ];
    }

    public function getCurrency(): Currency
    {
        return Currency::query()->find('usd');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orderProducts(): HasMany
    {
        return $this->hasMany(OrderProduct::class);
    }
}
