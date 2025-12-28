<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Models\Contracts\HasCurrency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Squire\Models\Currency;

class Product extends Model implements HasCurrency
{
    use HasFactory;
    public function casts(): array
    {
        return [
            'price' => MoneyCast::class,
            'stock_quantity' => 'integer',
        ];
    }

    public function getCurrency(): Currency
    {
        return Currency::query()->find('usd');
    }
}
