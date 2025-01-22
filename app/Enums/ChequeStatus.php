<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ChequeStatus:string implements HasColor,HasLabel
{
    case ISSUED = 'issued'; // صادر شده
    case CLEARED = 'cleared'; // وصول شده
    case Paid = 'paid'; // وصول شده
    case BOUNCED = 'bounced'; // برگشت خورده
    case BLOCKED = 'blocked'; // مسدود شده
    case PENDING = 'pending'; // در حال انتظار
    case CANCELLED = 'cancelled'; // لغو شده
    case POST_DATED = 'post_dated'; // تاریخ آینده

    public function getLabel(): ?string
    {
        return match ($this) {
            self::ISSUED => 'Issued',
            self::CLEARED => 'Cleared',
            self::BOUNCED => 'Bounced',
            self::BLOCKED => 'Blocked',
            self::PENDING => 'Pending',
            self::CANCELLED => 'Cancelled',
            self::POST_DATED => 'Post_dated',
            self::Paid => 'paid',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::ISSUED => 'info',
            self::CLEARED => 'success',
            self::BOUNCED => 'danger',
            self::BLOCKED => 'warning',
            self::PENDING => 'info',
            self::CANCELLED => 'danger',
            self::POST_DATED => 'warning',
            self::Paid => 'success',
        };
    }


}
