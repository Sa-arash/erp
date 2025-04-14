<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Product;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class StockAlert extends BaseWidget
{
    use HasWidgetShield;

    protected static ?string $heading = 'Stock Alert';
    protected int | string | array $columnSpan = 2;
    public function table(Table $table): Table
    {

        return $table
        ->query(
            Product::withCount('assets')
//             ->havingRaw('assets_count < stock_alert_threshold')
              )
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('sku')->label('SKU'),
                Tables\Columns\TextColumn::make('title')->label('Material Specification')->searchable(),
                Tables\Columns\ImageColumn::make('image')->defaultImageUrl(asset('img/images.jpeg'))->state(function ($record){
                    return $record->media->first()?->original_url;
                }),
                Tables\Columns\TextColumn::make('account.title')->label('Category Title')->sortable(),
                Tables\Columns\TextColumn::make('subAccount.title')->label('Sub Category Title')->sortable(),
                Tables\Columns\TextColumn::make('product_type'),
                Tables\Columns\TextColumn::make('count')->numeric()->state(fn($record) => $record->assets->count())->label('Quantity')->badge()
                ->color(fn($record)=>$record->assets->count()>$record->stock_alert_threshold ? 'success' : 'danger')->tooltip(fn($record)=>'Stock Alert:'.$record->stock_alert_threshold),
            ])->filters([
                Tables\Filters\SelectFilter::make('department_id')->label('Department')->options(getCompany()->departments->pluck('title','id'))->searchable()->preload()
            ],getModelFilter());
    }
}
