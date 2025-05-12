<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum LoanStatus:string implements HasColor,HasLabel
{
    case Waiting = 'waiting';
    case Rejected = 'rejected';
    case Accepted = 'accepted';
    case Progressed = 'progressed';
    case Finished = 'finished';
    case ApproveManager = 'ApproveManager';
    case ApproveAdmin = 'ApproveAdmin';
    case ApproveFinance = 'ApproveFinance';
    public function getLabel(): ?string
    {
        return match ($this){
            self::Waiting => 'Waiting',
            self::Rejected => 'Rejected',
            self::Accepted => 'Accepted',
            self::Progressed => 'Progressed',
            self::Finished => 'Finished',
            self::ApproveManager => 'Approve Manager',
            self::ApproveAdmin => 'Approve Admin',
            self::ApproveFinance => 'Approve Finance',

        };
    }
    public function getColor(): string|array|null
    {
        return match($this){
            self::Waiting => 'info',
            self::Rejected => 'danger',
            self::Accepted => 'success',
            self::Progressed => 'warning',
            self::Finished => 'success',
            self::ApproveManager => 'success',
            self::ApproveAdmin => 'success',
            self::ApproveFinance => 'success',
        };
    }

}
