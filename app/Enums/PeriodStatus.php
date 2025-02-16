<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PeriodStatus:string implements HasColor,HasLabel
{
    case Before = 'Before';
    case During = 'During';
    case End = 'End';

    public function getLabel(): ?string
    {
        return match ($this){
            self::Before => 'Setup Prerequisites',
            self::During => 'Current Fiscal Year',
            self::End => 'Closed',


        };
    }
    public function getColor(): string|array|null
    {
        return match($this){
            self::Before => 'info',
            self::During => 'success',
            self::End => 'danger',

        };
    }

}
