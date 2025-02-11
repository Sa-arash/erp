<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class StockAlert extends BaseWidget
{
    protected int | string | array $columnSpan = 2;
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::whereHas('assets', function (Builder $query) {
                    $query->selectRaw('COUNT(*) as asset_count');
                })
                ->withCount('assets') // محاسبه تعداد دارایی‌های مرتبط
                ->having('assets_count', '<', DB::raw('stock_alert_threshold'))    

            )
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\ImageColumn::make('image'),
                Tables\Columns\TextColumn::make('title')->label('Product Name')->searchable(),
                Tables\Columns\TextColumn::make('account.title')->label('Category Title')->sortable(),
                Tables\Columns\TextColumn::make('subAccount.title')->label('Sub Category Title')->sortable(),
                Tables\Columns\TextColumn::make('product_type'),
                Tables\Columns\TextColumn::make('count')->numeric()->state(fn($record) => $record->assets->count())->label('Quantity')->badge()
                ->color(fn($record)=>$record->assets->count()>$record->stock_alert_threshold ? 'success' : 'danger')->tooltip(fn($record)=>'Stock Alert:'.$record->stock_alert_threshold),
                Tables\Columns\TextColumn::make('price')->numeric()->state(fn($record) => $record->assets->sum('price'))->label('Total Value')->badge()->color('success')
            ]);
    }
}
