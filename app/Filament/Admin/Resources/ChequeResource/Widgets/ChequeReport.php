<?php

namespace App\Filament\Admin\Resources\ChequeResource\Widgets;

use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Widget;

class ChequeReport extends Widget
{
    use HasWidgetShield;

    protected int | string | array $columnSpan='full';
    protected static string $view = 'filament.admin.resources.cheque-resource.widgets.cheque-report';
}
