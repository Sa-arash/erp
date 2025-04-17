<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum TaskStatus:string implements HasColor,HasLabel,HasIcon
{
    case Processing = 'Processing';
    case Canceled = 'Canceled';
    case Completed = 'Completed';
    public function getLabel(): ?string
    {
        return match ($this){
            self::Processing => 'Processing',
            self::Canceled => 'Canceled',
            self::Completed => 'Completed',
        };
    }
    public function getColor(): string|array|null
    {
        return match($this){
            self::Processing => 'info',
            self::Canceled => 'danger',
            self::Completed => 'success',
        };
    }
    public function getIcon(): ?string
    {
        return match($this){
            self::Processing => 'heroicon-o-no-symbol',
            self::Canceled => 'heroicon-o-x-circle',
            self::Completed => 'heroicon-o-check-circle',
        };
    }


}
