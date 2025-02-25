<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum TaskStatus:string implements HasColor,HasLabel
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
            self::Canceled => 'Canceled',
            self::Completed => 'success',
        };
    }


}
