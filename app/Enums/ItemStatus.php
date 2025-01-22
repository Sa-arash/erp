<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ItemStatus:string implements HasColor,HasLabel
{

    case Purchased = 'purchased';
    case Assigned = 'assigned';
    case Not_Purchased = 'not_purchased';
    case Rejected = 'rejected';

    public function getLabel(): ?string
    {
        return match ($this){
            self::Purchased => 'Purchased',
            self::Assigned => 'Assigned',
            self::Not_Purchased => 'Not_Purchased',
            self::Rejected => 'Rejected',


        };
    }
    public function getColor(): string|array|null
    {
        return match($this){
            self::Purchased => 'primary',
            self::Assigned => 'success',
            self::Not_Purchased => 'info',
            self::Rejected => 'danger',

        };
    }
}
