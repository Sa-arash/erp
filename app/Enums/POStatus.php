<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum POStatus:string implements HasColor,HasLabel
{
    case Requested = 'Requested';
    case FinishedOperation = 'FinishedOperation';
    case FinishedCeo = 'FinishedCeo';
    case Finished = 'Finished';
    case Rejected = 'Rejected';
    public function getLabel(): ?string
    {
        return match ($this){
            self::Requested => 'Requested',
            self::FinishedOperation => 'Approved By Head Of Department',
            self::FinishedCeo => 'Approved By CEO',
            self::Finished => 'Finished',
            self::Rejected => 'Rejected',
        };
    }
    public function getColor(): string|array|null
    {
        return match($this){
            self::Requested => 'info',
            self::FinishedOperation => 'success',
            self::FinishedCeo => 'success',
            self::Finished => 'success',
            self::Rejected => 'danger',
        };
    }


}
