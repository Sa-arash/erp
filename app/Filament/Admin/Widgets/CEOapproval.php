<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Product;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use Filament\Facades\Filament;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater as ComponentsRepeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Repeater;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class CEOapproval extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';
    public function table(Table $table): Table
    {
        return $table
            ->query(
                PurchaseRequest::query()->where('company_id', getCompany()->id)->where('status', '!=', 'FinishedCeo')
            )
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('purchase_number')->label('PR NO')->searchable(),

                Tables\Columns\TextColumn::make('request_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('employee.fullName')
                    ->searchable(),
                Tables\Columns\TextColumn::make('department.title')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('total'),
                Tables\Columns\TextColumn::make('warehouse_decision')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('warehouse_status_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('department_manager_status_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('ceo_status_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('purchase_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),


            ])

            ->actions([
                Action::make('approval')->modalWidth(MaxWidth::Full)->form(
                    function ($record) {

                        return [
                            ComponentsRepeater::make('items')->formatStateUsing(fn($record) => $record->items->toArray())->schema([

                                Select::make('product_id')
                                    ->label('Product')->options(function () {
                                        $products = getCompany()->products;
                                        $data = [];
                                        foreach ($products as $product) {
                                            $data[$product->id] = $product->title . " (" . $product->sku . ")";
                                        }
                                        return $data;
                                    })->required()->searchable()->preload(),

                                TextInput::make('description')
                                    ->label('Description')
                                    ->required(),

                                Select::make('unit_id')
                                    ->searchable()
                                    ->preload()
                                    ->label('Unit')
                                    ->options(getCompany()->units->pluck('title', 'id'))
                                    ->required(),
                                TextInput::make('quantity')
                                    ->required()->live()
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(','),

                                TextInput::make('estimated_unit_cost')
                                    ->label('Estimated Unit Cost')->live()
                                    ->numeric()
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(','),

                                Select::make('project_id')
                                    ->searchable()
                                    ->preload()
                                    ->label('Project')
                                    ->options(getCompany()->projects->pluck('name', 'id')),

                                    Placeholder::make('total')
                                    ->content(fn($state, Get $get) => number_format(((int)str_replace(',', '', $get('quantity'))) * ((int)str_replace(',', '', $get('estimated_unit_cost'))))),
                                    Placeholder::make('stock in')
                                    ->content(fn($record, Get $get) => number_format(Product::find($get('product_id'))->assets->count())),

                                TextInput::make('ceo_comment'),
                                Select::make('ceo_decision')->options([
                                    // 'purchase',
                                    'approve' => 'approve',
                                    'reject' =>'reject',
                                    // 'assigne',
                                ])->required(),

                                Hidden::make('company_id')
                                    ->default(Filament::getTenant()->id)
                                    ->required(),
                            ])
                                ->columns(10)


                                ->columnSpanFull(),


                        ];
                    }
                )->action(function (array $data, $record): void {
                    // dd($data['items']);
                    PurchaseRequestItem::query()->where('purchase_request_id', $record->id)->delete();

                    foreach ($data['items'] as $item) {
                        $record->items()->create([
                            ...$item
                        ]);
                    }
                    $record->update([
                        'status' => 'FinishedCeo',
                    ]);
                    // $record->items()->attach($data);
                    // $record->save();
                })
            ])
        ;
    }
}
