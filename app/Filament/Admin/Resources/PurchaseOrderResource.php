<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PurchaseOrderResource\Pages;
use App\Filament\Admin\Resources\PurchaseOrderResource\RelationManagers;
use App\Models\Account;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use Closure;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
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
    protected static ?string $navigationGroup = 'Logistic Management';
    protected static ?int $navigationSort = 6;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Payment')->schema([
                    Select::make('account_id')
                    ->label('Payment Account')
                    ->options(
                        function () {
                            $accounts = getCompany()->accounts->whereIn('stamp', ['Bank', 'Cash'])->pluck('id')->toArray();
                            return Account::query()->whereIn('id', $accounts)
                                ->orWhereIn('parent_id', $accounts)
                                ->orWhereHas('account', function ($query) use ($accounts) {
                                    return $query->whereIn('parent_id', $accounts)->orWhereHas('account', function ($query) use ($accounts) {
                                        return $query->whereIn('parent_id', $accounts);
                                    });
                                })
                                ->get()->pluck('name', 'id')->toArray();
                        }



                    )
                        ->searchable()
                        ->preload()
                        ->required(),
                        // Forms\Components\Checkbox::make('Cheque')->inline()->live(),
                        // Forms\Components\Section::make([
                        //     Forms\Components\Fieldset::make('cheque')->relationship('account.cheque')->schema([
                        //         Forms\Components\TextInput::make('cheque_number')->required()->maxLength(255),
                        //         Forms\Components\TextInput::make('amount')->default(function (Get $get) {

                        //             if ($get('debtor') > 0) {
                        //                 return $get('debtor');
                        //             }
                        //             if ($get('creditor') > 0) {
                        //                 return $get('creditor');
                        //             }
                        //         })->mask(RawJs::make('$money($input)'))->stripCharacters(',')->required()->numeric(),
                        //         Forms\Components\DatePicker::make('issue_date')->required(),
                        //         Forms\Components\DatePicker::make('due_date')->required(),
                        //         Forms\Components\TextInput::make('payer_name')->required()->maxLength(255),
                        //         Forms\Components\TextInput::make('payee_name')->required()->maxLength(255),
                        //         Forms\Components\TextInput::make('bank_name')->maxLength(255),
                        //         Forms\Components\TextInput::make('branch_name')->maxLength(255),
                        //         Forms\Components\Textarea::make('description')->columnSpanFull(),
                        //         Forms\Components\ToggleButtons::make('type')->options([0 => 'Receivable', 1 => 'Payable'])->inline()->grouped()->required(),
                        //         Forms\Components\Hidden::make('company_id')->default(getCompany()->id)
                        //     ]),
                        // ])->collapsible()->persistCollapsed()->visible(fn(Forms\Get $get) => $get('Cheque')),
                        Forms\Components\Select::make('vendor_id')->label('Vendor')

                        ->options((getCompany()->parties->where('type', 'vendor')->pluck('info','id')))

                        ->searchable()
                        ->preload()
                        ->required(),
                        Forms\Components\Select::make('currency')->required()->required()->options(getCurrency())->searchable()->preload(),
                        Forms\Components\TextInput::make('exchange_rate')
                        ->required()->default(1)
                        ->numeric(),
                ])->columns(5),
                Section::make('Request')->schema([
                    Forms\Components\Select::make('prepared_by')->live()
                        ->searchable()
                        ->preload()
                        ->required()
                        ->options(getCompany()->employees->pluck('fullName', 'id'))
                        ->default(fn() => auth()->user()->employee->id),

                    Forms\Components\TextInput::make('purchase_orders_number')->default(function () {
                        $puncher = PurchaseOrder::query()->where('company_id', getCompany()->id)->latest()->first();
                        if ($puncher) {
                            return  generateNextCodePO($puncher->purchase_orders_number);
                        } else {
                            return "0001";
                        }
                    })->label('Po Number')
                        ->required()
                        ->unique(ignoreRecord: true, modifyRuleUsing: function (Unique $rule) {
                            return $rule->where('company_id', getCompany()->id);
                        })
                        ->maxLength(50),



                    Forms\Components\DatePicker::make('date_of_delivery')
                        ->required(),
                    Forms\Components\TextInput::make('location_of_delivery')
                        ->maxLength(255),



                        Forms\Components\DatePicker::make('date_of_po')->default(now())
                        ->label('Date of PO')
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
                        ->afterStateUpdated(function (Set $set, $state) {
                            if ($state){
                                $record=PurchaseRequest::query()->with('bid')->firstWhere('id',$state);
                                if ($record->bid){
                                    $data=[];
                                    foreach ($record->bid->quotation?->quotationItems->toArray() as $item){
                                        $prItem= PurchaseRequestItem::query()->firstWhere('id',$item['purchase_request_item_id']);
                                        $item['quantity']=$prItem->quantity;
                                        $item['unit_id']=$prItem->unit_id;
                                        $item['unit_price']=number_format($item['unit_rate']);
                                        $q=$prItem->quantity;
                                        $price=$item['unit_rate'];
                                        $tax=$item['taxes'];
                                        $freights=$item['freights'];
                                        $item['product_id']=$prItem->product_id;
                                        $item['total']=number_format(($q * $price) + (($q * $price * $tax)/100) + (($q * $price * $freights)/100));
                                        $data[]=$item;
                                    }
                                    $set('RequestedItems',$data);
                                }else{
                                    $set('RequestedItems',$record->items->where('status','approve')->toArray()) ;
                                }
                            }
                        })
                        ->options(getCompany()->purchaseRequests->pluck('purchase_number', 'id')),


                    Forms\Components\Hidden::make('company_id')
                        ->default(getCompany()->id)
                        ->required(),



                    Repeater::make('RequestedItems')->defaultItems(0)->required()->relationship('items')
                        // ->formatStateUsing(fn(Get $get) => dd($get('purchase_request_id')):'')
                        ->schema([
                            Forms\Components\Select::make('product_id')
                                ->label('Product')->options(function ($state) {
                                    $products = getCompany()->products->where('id', $state);
                                    $data = [];
                                    foreach ($products as $product) {
                                        $data[$product->id] = $product->title . " (sku:" . $product->sku . ")";
                                    }
                                    return $data;
                                })->required()->searchable()->preload(),
                            Forms\Components\TextInput::make('description')->label('Description')->required(),
                            Forms\Components\Select::make('unit_id')->required()->searchable()->preload()->label('Unit')->options(getCompany()->units->pluck('title', 'id')),
                            Forms\Components\TextInput::make('quantity')->required()->live(true),
                            Forms\Components\TextInput::make('unit_price')->afterStateUpdated(function ($state,Set $set, Get $get){
                                $freights = $get('taxes') === null ? 0 : (float) $get('taxes');
                                $q = $get('quantity');
                                $tax = $get('taxes') === null ? 0 : (float)$get('taxes');
                                $price = $state !== null ? str_replace(',', '', $state) : 0;
                                $set('total', number_format(($q * $price) + (($q * $price * $tax)/100) + (($q * $price * $freights)/100)));
                            })->live(true)
                                ->readOnly()
                                ->numeric()
                                ->mask(RawJs::make('$money($input)'))
                                ->stripCharacters(',')->label('Final Price'),
                            Forms\Components\TextInput::make('taxes')->afterStateUpdated(function ($state,Set $set, Get $get){
                                $freights = $get('freights') === null ? 0 : (float)$get('freights');
                                $q = $get('quantity');
                                $tax = $state === null ? 0 : (float)$state;
                                $price = $get('unit_rate') !== null ? str_replace(',', '', $get('unit_rate')) : 0;
                                $set('total', number_format(($q * $price) + (($q * $price * $tax)/100) + (($q * $price * $freights)/100)));
                            })->live(true)
                                ->prefix('%')
                                ->numeric()
                                ->required()
                                ->rules([
                                    fn(): Closure => function (string $attribute, $value, Closure $fail) {
                                        if ($value < 0) {
                                            $fail('The :attribute must be greater than 0.');
                                        }
                                        if ($value > 100) {
                                            $fail('The :attribute must be less than 100.');
                                        }
                                    },
                                ])
                                ->mask(RawJs::make('$money($input)'))
                                ->stripCharacters(','),
                            Forms\Components\TextInput::make('freights')->afterStateUpdated(function ($state,Set $set, Get $get){
                                $freights = $state === null ? 0 : (float) $state;
                                $q = $get('quantity');
                                $tax = $get('taxes') === null ? 0 : (float)$get('taxes');
                                $price = $get('unit_rate') !== null ? str_replace(',', '', $get('unit_rate')) : 0;
                                $set('total', number_format(($q * $price) + (($q * $price * $tax)/100) + (($q * $price * $freights)/100)));
                            })->live(true)
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

                            TextInput::make('total')->readOnly(),

                        ])
                        ->columns(9)
                        ->columnSpanFull(),
                ])->columns(3)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('account.name'),
                Tables\Columns\TextColumn::make('date_of_delivery')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location_of_delivery')
                    ->searchable(),
                Tables\Columns\TextColumn::make('po_no')
                    ->searchable(),
                Tables\Columns\TextColumn::make('currency')
                    ->searchable(),
                Tables\Columns\TextColumn::make('exchange_rate')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_of_po')
                ->label('Date Of PO')
                    ->date()
                    ->sortable(),
                Tables\Columns\Textcolumn::make('status')
                    ->label('Status'),


                Tables\Columns\TextColumn::make('purchase_request_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('vendor_id')
                    ->numeric()
                    ->sortable(),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('registration')->label('Registration assets')->url(fn($record)=> AssetResource::getUrl('create',['po'=>$record->id]))
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
