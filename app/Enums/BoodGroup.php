<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum BoodGroup:string implements HasColor,HasLabel
{

    case Aposetive = 'A+';
    case Anegetive = 'A-';
    case Bposetive = 'B+';
    case Bnegetive = 'B-';
    case ABposetive = 'AB+';
    case ABnegetive = 'AB-';

    public function getLabel(): ?string
    {
        return match ($this){
            self::Aposetive => 'A+',
            self::Anegetive => 'A-',
            self::Bposetive => 'B+',
            self::Bnegetive => 'B-',
            self::ABposetive => 'AB+',
            self::ABnegetive => 'AB-',

        };
    }
    public function getColor(): string|array|null
    {
        return match($this){
            self::Aposetive => 'success',
            self::Anegetive => 'danger',
            self::Bposetive => 'info',
            self::Bnegetive => 'primary',
            self::ABposetive => 'warning',
            self::ABnegetive => 'secondary',
        };
    }
}
