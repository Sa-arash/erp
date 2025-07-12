<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\GrnResource\Pages;
use App\Filament\Admin\Resources\GrnResource\RelationManagers;
use App\Models\Currency;
use App\Models\Grn;
use App\Models\Parties;
use App\Models\PurchaseOrder;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconSize;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Http\Request;

class GrnResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Grn::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $label = "Good Receipt Note";
    protected static ?string $navigationGroup = 'Logistic Management';
    protected static ?int $navigationSort = 3;

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'Head Logistic',
            'Finance',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema(fn(  $record)=> [
                Forms\Components\Select::make('purchase_order_id')->live()->afterStateUpdated(function ($state, Set $set) {
                    $PO = PurchaseOrder::query()->with('items')->firstWhere('id', $state);
                    if ($PO) {
                        $set('number', $PO->purchase_orders_number);
                        $items = $PO->items->toArray();
                        foreach ($items as $index => &$item) {
                            $item['row_number'] = $index + 1;
                        }
                        $set('RequestedItems', $items);
                    }
                })->searchable()->preload()->label('PO No')->required()->prefix('ATGT/UNC/')->options(fn($record)=> $record ==null ?PurchaseOrder::query()->where('status', 'Approved')->where('company_id', getCompany()->id)->pluck('purchase_orders_number', 'id'):PurchaseOrder::query()->where('id',$record->purchase_order_id)->pluck('purchase_orders_number', 'id')),
                Forms\Components\Select::make('manager_id')->default(getEmployee()?->id)->label('Process By')->required()->options(getCompany()->employees()->where('id', getEmployee()?->id)->pluck('fullName', 'id'))->preload()->searchable(),
                Forms\Components\TextInput::make('number')->prefix('ATGT/UNC/')->readOnly()->required()->maxLength(255),
                Forms\Components\DateTimePicker::make('received_date')->required()->seconds(false)->default(now()),
                Repeater::make('RequestedItems')->reorderableWithDragAndDrop(false)->defaultItems(1)->required()
                    ->default(function (Request $request, Set $set) {

                        $PO = (PurchaseOrder::query()->with('bid')->firstWhere('id', $request->PO));
                        if ($PO) {
                            $set('number', $PO->purchase_orders_number);
                            $set('purchase_order_id', $PO->id);
                            $items = $PO->items->toArray();
                            foreach ($items as $index => &$item) {
                                $item['row_number'] = $index + 1;
                            }
                          return  $items;

                        }

                    })
                    ->when(
                        fn () => $record !== null,
                        fn ($component) => $component->relationship('items')
                    )
                    // ->formatStateUsing(fn(Get $get) => dd($get('purchase_request_id')):'')
                    ->schema([
//                                        Forms\Components\Hidden::make('row_number')
//                                            ->default(fn (Get $get, \Livewire\Component $livewire) => count($get('../items') ?? []) + 1)
//                                        ->formatStateUsing(fn ($state, Get $get) => $state ?? count($get('../items') ?? []) + 1),
                        Forms\Components\Select::make('product_id')->columnSpan(3)->label('Product')->prefix(fn(Get $get) => $get('row_number'))->options(function ($state) {
                            if ($state) {
                                $products = getCompany()->products->where('id', $state);
                            } else {
                                $products = getCompany()->products;
                            }
                            $data = [];
                            foreach ($products as $product) {
                                $data[$product->id] = $product->info;
                            }
                            return $data;
                        })->required()->searchable()->preload(),
                        Forms\Components\TextInput::make('description')->label('Description')->columnSpan(7)->required(),
                        Forms\Components\Select::make('unit_id')->columnSpan(2)->required()->searchable()->preload()->label('Unit')->options(getCompany()->units->pluck('title', 'id')),
                        Forms\Components\TextInput::make('quantity')->numeric()->required(),
                        Forms\Components\TextInput::make('unit_price')->numeric()->required()->mask(RawJs::make('$money($input)'))->stripCharacters(',')->label('Unit Price'),
                        Forms\Components\TextInput::make('taxes')->default(0)->prefix('%')->numeric()->required()->rules([
                            fn(): \Closure => function (string $attribute, $value, \Closure $fail) {
                                if ($value < 0) {
                                    $fail('The :attribute must be greater than 0.');
                                }
                                if ($value > 100) {
                                    $fail('The :attribute must be less than 100.');
                                }
                            },
                        ])->mask(RawJs::make('$money($input)'))->stripCharacters(','),
                        Forms\Components\TextInput::make('freights')->default(0)->required()->numeric()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
                        Forms\Components\Hidden::make('project_id')->label('Project'),
                        Forms\Components\Select::make('vendor_id')->live(true)->label('Vendor')->options((getCompany()->parties->where('type', 'vendor')->pluck('info', 'id')))->searchable()->preload()->required()->columnSpan(2)->afterStateUpdated(function (Set $set, $state, Get $get) {
                            $vendor = Parties::query()->with('currency')->firstWhere('id', $state);
                            if ($vendor) {
                                $set('currency_id', $vendor->currency_id);
                                $set('exchange_rate', $vendor->currency?->exchange_rate);
                            }
                        }),
                        Select::make('currency_id')->label('Currency')->afterStateUpdated(function (Set $set, $state, Get $get) {
                            $currency = Currency::find($state);
                            if ($currency !== null) {
                                $set('exchange_rate', $currency->exchange_rate);
                            }
                        })->required()->live(true)->options(getCompany()->currencies->pluck('name', 'id'))->searchable()->preload()->columnSpan(2),
                        TextInput::make('exchange_rate')->readOnly()->required()->numeric()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
                        TextInput::make('total')->hintAction(Forms\Components\Actions\Action::make('Calculate')->action(function (Set $set,Get $get){
                            $freights = $get('taxes') === null ? 0 : (float)$get('taxes');
                            $q = $get('quantity');
                            $tax = $get('taxes') === null ? 0 : (float)$get('taxes');
                            $price = $get('unit_price') !== null ? str_replace(',', '', $get('unit_price')) : 0;
                            $total = (($q * $price) + (($q * $price * $tax) / 100) + (($q * $price * $freights) / 100)) * (float)$get('exchange_rate');
                            $set('total', number_format($total, 2));
                        })->icon('heroicon-o-calculator')->color('danger')->iconSize(IconSize::Large))->required()->mask(RawJs::make('$money($input)'))->stripCharacters(',')->columnSpan(2)->readOnly(),
                        Select::make('employee_id')->label(' Purchaser')->columnSpan(3)->required()->options(getCompany()->employees()->whereIn('department_id',getCompany()->purchaser_department)->pluck('fullName', 'id'))->searchable()->preload()
                    ])->live()
                    ->columns(13)
                    ->columnSpanFull()->addable(false),
                Section::make()->schema([
                    TextInput::make('totals')->prefix(defaultCurrency()?->name)->inlineLabel()->label('Sub Total')->hintAction(Forms\Components\Actions\Action::make('Calculate')->action(function (Set $set,Get $get){
                        $total= collect($get('RequestedItems'))->map(fn($item) => $item['total']? str_replace(',','',$item['total']):0)->sum();
                        $set('totals',number_format($total,2));
                    })->icon('heroicon-o-calculator')->color('danger')->iconSize(IconSize::Large))->dehydrated(false)->readOnly()
                ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make(getRowIndexName())->rowIndex(),
                Tables\Columns\TextColumn::make('purchaseOrder.purchase_orders_number')->label('PO No')->prefix('ATGT/UNC/')->sortable(),
                Tables\Columns\TextColumn::make('number')->prefix('ATGT/UNC/')->sortable()->label('GRN No'),
                Tables\Columns\TextColumn::make('manager.fullName')->label('Process By')->sortable(),
                Tables\Columns\TextColumn::make('received_date') ->dateTime()->searchable(),
                Tables\Columns\TextColumn::make('items_sum_total')->numeric(2)->sum('items','total')->label('Total')->sortable(),
//                Tables\Columns\TextColumn::make('s')->state(fn($record)=>dd($record))->label('Total')->searchable(),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('prPDF')->label('Print ')->iconSize(IconSize::Large)->icon('heroicon-s-printer')->url(fn($record) => route('pdf.grn', ['id' => $record->id,'company'=>getCompany()->id]))->openUrlInNewTab(),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()->hidden(function ($record){
                    return $record->items()->where('receive_status','!=','Approved')->count();
                }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemsGrnRelationManager::class
        ];
    }
    public static function getNavigationBadge(): ?string
    {
        return PurchaseOrder::query()->where('company_id',getCompany()->id)->where('status','Approved')->count();
    }
    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'danger';
    }
    public static function getNavigationBadgeTooltip(): ?string
    {
        return "Count of PO ready for GRM";
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGrns::route('/'),
            'create' => Pages\CreateGrn::route('/create'),
            'view' => Pages\ViewGrn::route('/{record}'),
            'edit' => Pages\EditGrn::route('/{record}/edit'),
        ];
    }
}
