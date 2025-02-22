<?php

namespace App\Filament\Admin\Resources\ChequeResource\Widgets;

use Filament\Widgets\Widget;

class ChequeReport extends Widget
{
    protected int | string | array $columnSpan='full';
    protected static string $view = 'filament.admin.resources.cheque-resource.widgets.cheque-report';
}
