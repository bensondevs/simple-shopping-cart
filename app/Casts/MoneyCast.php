<?php

namespace App\Casts;

use Akaunting\Money\Currency;
use App\Models\Contracts\HasCurrency;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class MoneyCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?float
    {
        if (blank($value)) {
            return null;
        }

        $currency = $this->getCurrencyFromModel($model);

        $value = intval($value);

        $value /= $currency->getSubunit();

        return round($value, $currency->getPrecision());
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?int
    {
        if (blank($value)) {
            return null;
        }

        $currency = $this->getCurrencyFromModel($model);

        $value = floatval($value);

        $value *= $currency->getSubunit();

        return round($value);
    }

    protected function getCurrencyFromModel(Model $model): Currency
    {
        $currency = $model instanceof HasCurrency
            ? $model->getCurrency()
            : null;

        return new Currency(strtoupper($currency?->getKey() ?? 'usd'));
    }
}
