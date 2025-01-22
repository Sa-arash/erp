<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum GenderEnum:string implements HasIcon,HasColor,HasLabel
{
    case true = 'man';
    case false = 'woman';
    public function getIcon(): ?string
    {
        return match ($this){
            self::true => 'man',
            self::false => 'woman',
        };
    }
    public function getColor(): string|array|null
    {
        return match($this){
            self::true => 'warning',
            self::false => 'success',
        };
    }
    public function getLabel(): string|null
    {
        return match($this){
            self::true => 'man',
            self::false => 'woman',
        };
    }
}
