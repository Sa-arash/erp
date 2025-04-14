<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum LeaveStatus2:string implements HasColor,HasLabel
{
    case Pending = 'pending';
    case Rejected = 'rejected';
    case Accepted = 'accepted';
    case ApproveHead = 'approveHead';
    public function getLabel(): ?string
    {
        return match ($this){
            self::Pending => 'Pending',
            self::Rejected => 'Rejected',
            self::Accepted => 'Approved',
            self::ApproveHead => 'ApproveHead',
        };
    }
    public function getColor(): string|array|null
    {
        return match($this){
            self::Pending => 'info',
            self::Rejected => 'danger',
            self::Accepted => 'success',
            self::ApproveHead => 'success',
        };
    }


}
