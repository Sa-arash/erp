<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ItemStatus:string implements HasColor,HasLabel
{

    case purchased = 'purchased';
    case assigned = 'assigned';
    case pending = 'pending';

    public function getLabel(): ?string
    {
        return match ($this){
            self::purchased => 'purchased',
            self::assigned => 'assigned',
            self::pending => 'pending',


        };
    }
    public function getColor(): string|array|null
    {
        return match($this){
            self::purchased => 'primary',
            self::assigned => 'success',
            self::pending => 'info',

        };
    }
}
