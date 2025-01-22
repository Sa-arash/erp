<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum Recurring:string implements HasIcon,HasColor,HasLabel
{
    case daily = 'daily';
    case monthly = 'monthly';
    case weekly = 'weekly';
    case none = 'none';
    public function getIcon(): ?string
    {
        return match ($this){
            self::daily => 'daily',
            self::monthly => 'monthly',
            self::weekly => 'weekly',
            self::none => 'none',
        };
    }
    public function getColor(): string|array|null
    {
        return match($this){
            self::daily => 'info',
            self::monthly => 'warning',
            self::weekly => 'danger',
            self::none => 'success',
        };
    }
    public function getLabel(): string|null
    {
        return match($this){
            self::daily => 'daily',
            self::monthly => 'monthly',
            self::weekly => 'weekly',
            self::none => 'none',
        };
    }
}
