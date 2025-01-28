<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PurchaseOrderResource\Pages;
use App\Filament\Admin\Resources\PurchaseOrderResource\RelationManagers;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use Closure;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\Rules\Unique;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;
    protected static ?string $navigationGroup = 'Stock Management';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Request')->schema([
                    Forms\Components\Select::make('prepared_by')->live()
                        ->searchable()
                        ->preload()
                        ->required()
                        ->options(getCompany()->employees->pluck('fullName', 'id'))
                        ->default(fn() => auth()->user()->employee->id),

                    Forms\Components\TextInput::make('purchase_orders_number')->default(function (){
                        $puncher= PurchaseOrder::query()->where('company_id',getCompany()->id)->latest()->first();
                        if ($puncher){
                            return  generateNextCodePO($puncher->purchase_orders_number);
                        }else{
                            return "0001";
                        }
                    })->label('Po Number')
                        ->required()
                        ->unique(ignoreRecord: true, modifyRuleUsing: function (Unique $rule) {
                            return $rule->where('company_id', getCompany()->id);
                        })
                        ->maxLength(50),

                    // prepared_by_logistic
                    // checked_by_finance
                    // approved_by

                    Forms\Components\Select::make('payment_type')->options(['Cheque' => 'Cheque', 'Cash' => 'Cash', 'BankTransfer' => 'BankTransfer', 'Other' => 'Other',])
                        ->searchable()
                        ->preload()
                        ->required(),
                    Forms\Components\DatePicker::make('date_of_delivery')
                        ->required(),
                    Forms\Components\TextInput::make('location_of_delivery')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('project_and_exp_code')
                        ->required()
                        ->maxLength(100),

                    Forms\Components\Select::make('currency')->required()->required()->options(getCurrency())->searchable()->preload(),

                    Forms\Components\TextInput::make('exchange_rate')
                        ->numeric(),
                    Forms\Components\DatePicker::make('date_of_po')->default(now())
                        ->required(),
                    // Forms\Components\Select::make('bid_id')
                    // ->relationship('bid', 'id')
                    // ->required(),
                    // Forms\Components\Select::make('quotation_id')
                    // ->relationship('quotation', 'id')
                    // ->required(),
                    Forms\Components\Select::make('purchase_request_id')->live()
                    ->label('purchase Request')
                        ->searchable()
                        ->preload()
                        ->afterStateUpdated(function (Set $set, $state, $component) {
                            $items = PurchaseRequestItem::where('purchase_request_id', $state)->get()->map(function ($item) {
                                $item->taxes = $item->freights = $item->unit_price = 0;
                                return $item;
                            })->toArray();

                            if (isset($items)) {
                                $set('RequestedItems', $items);
                            }
                        })
                        ->options(getCompany()->purchaseRequests->pluck('id', 'purchase_number'))
                        ->required(),
                    Forms\Components\Select::make('vendor_id')->label('Vendor')
                    ->options(getCompany()->parties->where('type','vendor')->pluck('name', 'id'))

                        ->searchable()
                        ->preload()
                        ->required(),


                    Forms\Components\Hidden::make('company_id')
                        ->default(getCompany()->id)
                        ->required(),



                    Repeater::make('RequestedItems')->defaultItems(0)
                        // ->formatStateUsing(fn(Get $get) => dd($get('purchase_request_id')):'')
                        ->relationship('items')
                        ->schema([
                            Forms\Components\Select::make('product_id')
                                ->disabled()
                                ->label('Product')->options(function () {
                                    $products = getCompany()->products;
                                    $data = [];
                                    foreach ($products as $product) {
                                        $data[$product->id] = $product->title . " (" . $product->sku . ")";
                                    }
                                    return $data;
                                })->required()->searchable()->preload(),

                            Forms\Components\TextInput::make('description')
                                ->disabled()
                                ->label('Description')
                                ->required(),

                            Forms\Components\Select::make('unit_id')
                                ->disabled()
                                ->searchable()
                                ->preload()
                                ->label('Unit')
                                ->options(getCompany()->units->pluck('title', 'id'))
                                ->required(),
                            Forms\Components\TextInput::make('quantity')
                                ->disabled()
                                ->required()->live()
                                ->mask(RawJs::make('$money($input)'))
                                ->stripCharacters(','),


                            Forms\Components\TextInput::make('unit_price')
                                ->required()
                                ->label('Unit Cost')
                                ->numeric()
                                ->mask(RawJs::make('$money($input)'))
                                ->stripCharacters(','),

                            Forms\Components\TextInput::make('taxes')
                                ->prefix('%')
                                ->numeric()
                                ->required()
                                ->rules([
                                    fn(): Closure => function (string $attribute, $value, Closure $fail) {
                                        if ($value <= 0) {
                                            $fail('The :attribute must be greater than 0.');
                                        }
                                        if ($value >= 100) {
                                            $fail('The :attribute must be less than 100.');
                                        }
                                    },
                                ])
                                ->mask(RawJs::make('$money($input)'))
                                ->stripCharacters(','),
                            Forms\Components\TextInput::make('freights')
                                ->required()
                                ->numeric()
                                ->mask(RawJs::make('$money($input)'))
                                ->stripCharacters(','),

                            Forms\Components\Select::make('project_id')
                                ->searchable()
                                ->preload()
                                ->disabled()
                                ->label('Project')
                                ->options(getCompany()->projects->pluck('name', 'id')),

                            Placeholder::make('total')
                                ->content(fn($state, Get $get) => number_format((((int)str_replace(',', '', $get('quantity'))) * ((int)str_replace(',', '', $get('estimated_unit_cost')))))),

                            Forms\Components\Hidden::make('company_id')
                                ->default(Filament::getTenant()->id)
                                ->required(),
                        ])
                        ->columns(9)
                        ->columnSpanFull(),
                ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('payment_type'),
                Tables\Columns\TextColumn::make('date_of_delivery')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location_of_delivery')
                    ->searchable(),
                Tables\Columns\TextColumn::make('project_and_exp_code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('po_no')
                    ->searchable(),
                Tables\Columns\TextColumn::make('currency')
                    ->searchable(),
                Tables\Columns\TextColumn::make('exchange_rate')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_of_po')
                    ->date()
                    ->sortable(),
                Tables\Columns\Textcolumn::make('status')
                    ->label('Status'),

                Tables\Columns\TextColumn::make('bid_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quotation_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('purchase_request_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('vendor_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('company.title')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchaseOrders::route('/'),
            'create' => Pages\CreatePurchaseOrder::route('/create'),
            'edit' => Pages\EditPurchaseOrder::route('/{record}/edit'),
        ];
    }
}
