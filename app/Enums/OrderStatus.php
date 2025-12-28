<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Cancelled = 'cancelled';

    public function badgeVariant(): string
    {
        return match ($this) {
            self::Paid => 'success',
            self::Cancelled => 'danger',
            self::Pending => 'warning',
        };
    }
}

