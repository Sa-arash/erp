<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PayrollStatus:string implements HasColor,HasLabel
{
    case Accepted = 'accepted';
    case Pending = 'pending';
    case Payed = 'payed';
    public function getLabel(): ?string
    {
        return match ($this){
            self::Pending => 'Pending',
            self::Accepted => 'Accepted',
            self::Payed => 'Payed',

        };
    }
    public function getColor(): string|array|null
    {
        return match($this){
            self::Pending => 'info',
            self::Accepted => 'success',
            self::Payed => 'warning',
        };
    }
}
