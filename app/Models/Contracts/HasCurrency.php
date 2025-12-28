<?php

namespace App\Models\Contracts;

use Squire\Models\Currency;

interface HasCurrency
{
    public function getCurrency(): Currency;
}
