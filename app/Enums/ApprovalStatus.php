<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ApprovalStatus:string implements HasColor,HasLabel
{
    case Pending = 'Pending';
    case NotApprove = 'NotApprove';
    case Approve = 'Approve';
    public function getLabel(): ?string
    {
        return match ($this){
            self::Pending => 'Pending',
            self::NotApprove => 'NotApprove',
            self::Approve => 'Approve',
        };
    }
    public function getColor(): string|array|null
    {
        return match($this){
            self::Pending => 'info',
            self::NotApprove => 'danger',
            self::Approve => 'success',
        };
    }


}
