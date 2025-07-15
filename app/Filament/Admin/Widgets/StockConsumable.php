<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Product;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class StockConsumable extends BaseWidget
{
    use HasWidgetShield;

    protected static ?string $heading = 'Stock Alert Consumable';
    protected int | string | array $columnSpan = 2;
    public function table(Table $table): Table
    {

        return $table->query(
            Product::select('products.*')
                ->selectRaw('(SELECT SUM(quantity) FROM inventories WHERE inventories.product_id = products.id) as inventories_quantity')
                ->where('product_type', 'consumable')
                ->groupBy('products.id','deleted_at', 'unit_id', 'created_at', 'updated_at', 'products.stock_alert_threshold', 'products.product_type', 'products.title', 'products.second_title', 'products.image', 'products.department_id', 'products.account_id', 'products.sku', 'products.sub_account_id', 'products.description', 'products.company_id')
                ->havingRaw('(inventories_quantity IS NULL OR inventories_quantity < stock_alert_threshold)')
                )
            ->columns([
                Tables\Columns\TextColumn::make('')->label('#')->rowIndex(),
                Tables\Columns\TextColumn::make('sku')->label('SKU'),
                Tables\Columns\TextColumn::make('title')->label('Material Specification')->searchable(),
                Tables\Columns\TextColumn::make('second_title')->label('Specification in Dari Language')->toggleable(true,false)->searchable(),
                Tables\Columns\ImageColumn::make('image')->action(Tables\Actions\Action::make('image')->modalSubmitAction(false)->infolist(function ($record){
                    if ($record->media->first()?->original_url){
                        return  [
                            Section::make([
                                ImageEntry::make('image')->label('')->width(650)->height(650)->columnSpanFull()->state($record->media->first()?->original_url)
                            ])
                        ];
                    }
                }))->defaultImageUrl(asset('img/images.jpeg'))->state(function ($record){
                    return $record->media->first()?->original_url;
                }),
                Tables\Columns\TextColumn::make('account.title')->label('Category Title')->sortable(),
                Tables\Columns\TextColumn::make('subAccount.title')->label('Sub Category Title')->sortable(),
                Tables\Columns\TextColumn::make('product_type')->state(function($record){
                    if ($record->product_type==='consumable'){
                        return 'Consumable';
                    }elseif($record->product_type   ==='unConsumable'){
                        return 'Non-Consumable';
                    }
                }),
                Tables\Columns\TextColumn::make('countInventory')->numeric()->state(fn($record) => $record->inventories()?->sum('quantity'))->label('Available')->badge(),
            ])->filters([
                Tables\Filters\SelectFilter::make('department_id')->label('Department')->options(getCompany()->departments->pluck('title','id'))->searchable()->preload(),

            ],getModelFilter());
    }
}
