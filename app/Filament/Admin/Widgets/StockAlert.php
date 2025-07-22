<?php

namespace App\Filament\Admin\Widgets;

use App\Models\AssetEmployeeItem;
use App\Models\Product;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class StockAlert extends BaseWidget
{
    use HasWidgetShield;

    protected static ?string $heading = 'Stock Alert  Non-Consumable';
    protected int | string | array $columnSpan = 2;
    public function table(Table $table): Table
    {

        return $table
            ->query(
                Product::select('products.*')
                    ->selectRaw('(SELECT COUNT(*) FROM assets WHERE assets.product_id = products.id) as assets_count')
                    ->where('product_type', 'unConsumable')
                    ->where('company_id', getCompany()->id)
                    ->groupBy('products.id', 'unit_id','deleted_at', 'created_at', 'updated_at', 'products.stock_alert_threshold', 'products.product_type', 'products.title', 'products.second_title', 'products.image', 'products.department_id', 'products.account_id', 'products.sku', 'products.sub_account_id', 'products.description', 'products.company_id')
                    ->havingRaw('assets_count < stock_alert_threshold')
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
                Tables\Columns\TextColumn::make('use')->numeric()->state(fn($record) => $record->assets->whereIn('status',['inuse'])->count())->label(' In Use')->badge()->color('warning'),
                Tables\Columns\TextColumn::make('storage')->numeric()->state(fn($record) => $record->assets->whereIn('status',['inStorageUsable','storageUnUsable'])->count())->label('In Storage')->badge()->color('warning'),
                Tables\Columns\TextColumn::make('count')->numeric()->state(fn($record) => $record->assets->count())->label('Quantity')->badge()->color(fn($record)=>$record->assets->count()>$record->stock_alert_threshold ? 'success' : 'danger')->tooltip(fn($record)=>'Stock Alert:'.$record->stock_alert_threshold),
            ])->filters([
                Tables\Filters\SelectFilter::make('department_id')->label('Department')->options(getCompany()->departments->pluck('title','id'))->searchable()->preload(),
            ],getModelFilter());
    }
}
