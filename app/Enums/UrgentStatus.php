<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum UrgentStatus:string implements HasColor,HasLabel
{
    case Pending = 'pending';
    case Rejected = 'rejected';
    case Accepted = 'accepted';
    case ApproveHead = 'approveHead';
    case NotReturned = 'Not Returned';
    case Returned = 'Returned';
    public function getLabel(): ?string
    {
        return match ($this){
            self::Pending => 'Pending',
            self::Rejected => 'Rejected',
            self::Accepted => 'Approved',
            self::ApproveHead => 'Approve Head',
            self::NotReturned => 'Not Returned',
            self::Returned => 'Returned',
        };
    }
    public function getColor(): string|array|null
    {
        return match($this){
            self::Pending => 'info',
            self::Rejected => 'danger',
            self::Accepted => 'success',
            self::ApproveHead => 'success',
            self::NotReturned => 'warning',
            self::Returned => 'success',
        };
    }


}
