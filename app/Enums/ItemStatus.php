<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ItemStatus:string implements HasColor,HasLabel
{

    case Reject = 'rejected';
    case pending = 'pending';
    case Approve = 'approve';

    public function getLabel(): ?string
    {
        return match ($this){
            self::Reject => 'Rejected',
            self::pending => 'Pending',
            self::Approve => 'Approved',
        };
    }
    public function getColor(): string|array|null
    {
        return match($this){
            self::pending => 'info',
            self::Reject => 'danger',
            self::Approve => 'success',

        };
    }
}
