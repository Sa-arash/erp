<?php

namespace App\Filament\Admin\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class MyLoan extends BaseWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->query(
                
            )
            ->columns([
                // ...
            ]);
    }
}
