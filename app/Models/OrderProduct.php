<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Models\Contracts\HasCurrency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Squire\Models\Currency;

class OrderProduct extends Model implements HasCurrency
{
    protected function casts(): array
    {
        return [
            'price' => MoneyCast::class,
        ];
    }

    public function getCurrency(): Currency
    {
        return Currency::query()->find('usd');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
