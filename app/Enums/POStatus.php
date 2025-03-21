<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum POStatus:string implements HasColor,HasLabel
{
    case Requested = 'Requested';
    case Clarification = 'Clarification';
    case Verification = 'Verification';
    case Approval = 'Approval';
    case Rejected = 'Rejected';
    public function getLabel(): ?string
    {
        return match ($this){
            self::Requested => 'Requested',
            self::Clarification => 'Clarification',
            self::Verification => 'Verification',
            self::Approval => 'Approval',
            self::Rejected => 'Rejected',
        };
    }
    public function getColor(): string|array|null
    {
        return match($this){
            self::Requested => 'info',
            self::Clarification => 'success',
            self::Verification => 'success',
            self::Approval => 'success',
            self::Rejected => 'danger',
        };
    }


}
