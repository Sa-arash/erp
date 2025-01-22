<?php

namespace App\Filament\Admin\Resources\ProductCategoryResource\Widgets;

use App\Filament\Admin\Resources\ProductCategoryResource;
use App\Filament\Admin\Resources\ProductResource;
use App\Models\Account;
use App\Models\ProductCategory;
use App\Models\ProductSubCategory;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Request;

class SubCategory extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ProductSubCategory::query()->where('company_id', getCompany()->id)
            )->headerActions([
                Tables\Actions\Action::make('new_SubCategory')->form([
                    TextInput::make('title')->label('Sub Category')->required(),
                    Select::make('product_category_id')->searchable()->preload()->required()->label('Parent Category')->options(ProductCategory::query()->where('company_id', getCompany()->id)->pluck('title', 'id'))
                ])->action(function ($data) {
                    $data['company_id'] = getCompany()->id;

                    $productCategory = ProductCategory::query()->with('account.childerns')->firstWhere('id', $data['product_category_id']);
                    $categoryAccount = Account::query()->create([
                        'name' => $data['title'],
                        'type' => 'debtor',
                        'stamp' => $data['title'],
                        'code' => $productCategory->account->childerns->last()?->code !== null ? generateNextCodeDote($productCategory->account->childerns->last()?->code):$productCategory->account?->code."0001",
                        'level' => 'detail',
                        'parent_id' => $productCategory->account_id,
                        'company_id' => getCompany()->id,
                    ]);
                    $data['account_id'] = $categoryAccount->id;
                    ProductSubCategory::query()->create($data);
                })
            ])->filters([
                Tables\Filters\SelectFilter::make('product_category_id')->label('Parent')->searchable()->preload()->options(ProductCategory::query()->where('company_id', getCompany()->id)->pluck('title', 'id'))
            ], Tables\Enums\FiltersLayout::AboveContent)
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('title')->label('Sub Category'),
                Tables\Columns\TextColumn::make('account.code')->badge()->label('Account Code'),
                Tables\Columns\TextColumn::make('Product')->label('Quantity')->url(fn($record) => ProductResource::getUrl('index', ['tableFilters[category_id][value]' => $record->id]))->color('aColor')->badge()->state(fn($record) => $record->products->count())->url(fn($record) => ProductResource::getUrl() . '?tableFilters[product_category_id][value]=' . $record->id),
            ]);
    }
}
