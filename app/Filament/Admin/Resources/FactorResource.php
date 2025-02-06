<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\FactorResource\Pages;
use App\Filament\Admin\Resources\FactorResource\RelationManagers;
use App\Models\Factor;
use App\Models\FinancialPeriod;
use App\Models\Parties;
use App\Models\Unit;
use Closure;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Hamcrest\Core\Set;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FactorResource extends Resource
{
    protected static ?string $model = Factor::class;
    protected static ?string $label = 'Invoice';
    protected static ?string $pluralLabel = 'Invoices';
    protected static ?string $navigationGroup = 'Finance Management';
    protected static ?string $navigationIcon = 'heroicon-s-document-currency-dollar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Invoice')->schema([
                        Forms\Components\Section::make([
                            Forms\Components\TextInput::make('title')->required()->maxLength(255),
                            Forms\Components\ToggleButtons::make('type')->live()->afterStateUpdated(function (Forms\Set $set, string $operation) {
                                // $set('party_id', null);
                                // $set('account_id', null);
                                // $set('to', null);
                                // $set('from', null);
                                if($operation =="create")
                                {
                                    $set('invoice.transactions', []);
                                }
                                // dd($set);
                                // debtor
                                // creditor
                            })->required()->default(0)->boolean('Income', 'Expense')->grouped(),
                            Forms\Components\Select::make('account_id')->label(fn(Forms\Get $get) => $get('type') === "1" ? "Income Account" : "Expence Account")->searchable()->required()->options(function (Forms\Get $get) {
                                $type = $get('type') === "1" ? "Income" : "Expense";
                                // dd();
                                return getCompany()->accounts->whereIn('group', [$type])->pluck('name', 'id');
                            })->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                $party = Parties::query()->firstWhere('id', $state);
                                if ($get('type') !== "1") {
                                    $set('to', getCompany()->AccountTitle);
                                } else {
                                    $set('from', getCompany()->AccountTitle);
                                }
                            })->live(true),


                            Forms\Components\Select::make('party_id')->label(fn(Forms\Get $get) => $get('type') === "1" ? "Customer" : "Vendor")->searchable()->required()->options(function (Forms\Get $get) {
                                $type = $get('type') === "1" ? "customer" : "vendor";
                                return getCompany()->parties->whereIn('type', [$type, 'both'])->pluck('info', 'id');
                            })->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                $party = Parties::query()->firstWhere('id', $state);
                                if ($get('type') === "1") {
                                    $set('to', $party?->name);
                                } else {
                                    $set('from', $party?->name);
                                }
                            })->live(true),

                        ])->columns(2),
                        Forms\Components\TextInput::make('from')->required()->maxLength(255),
                        Forms\Components\TextInput::make('to')->required()->maxLength(255),
                        Forms\Components\Repeater::make('items')->required()->relationship('items')->schema([
                            Forms\Components\TextInput::make('title')->required()->label('Invoice Item')->columnSpan(2),
                            Forms\Components\TextInput::make('quantity')->default(1)->numeric()->live(true)->required()->label('Quantity')->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                                $count = $get('quantity') === null ? 0 : (float)$get('quantity');
                                $unitPrice = $get('unit_price') === null ?  0 : (float)str_replace(',', '', $get('unit_price'));
                                $discount = $get('discount') === null ?  0 : (float)$get('discount');
                                $set('total', number_format(($count * $unitPrice) - (($count * $unitPrice) * $discount) / 100,2));
                            }),
                            Forms\Components\Select::make('unit_id')->label('Unit')->required()->options(Unit::query()->where('company_id', getCompany()->id)->pluck('title', 'id'))->searchable()->preload(),
                            Forms\Components\TextInput::make('unit_price')->default(0)->rules([
                                fn (): Closure => function (string $attribute, $value, Closure $fail) {
                                    if ($value <=0) {
                                        $fail('The :attribute is invalid.');
                                    }
                                },
                            ])->mask(RawJs::make('$money($input)'))->stripCharacters(',')->live(true)->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                                $count = $get('quantity') === null ? 0 : (float)$get('quantity');
                                $unitPrice = $get('unit_price') === null ?  0 : (float)str_replace(',', '', $get('unit_price'));
                                $discount = $get('discount') === null ?  0 : (float)$get('discount');
                                $set('total', number_format(($count * $unitPrice) - (($count * $unitPrice) * $discount) / 100,2));
                            })->required()->label('Unit Price'),
                            Forms\Components\TextInput::make('discount')->numeric()->live(true)->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                                $count = $get('quantity') === null ? 0 : (float)$get('quantity');
                                $unitPrice = $get('unit_price') === null ?  0 : (float)str_replace(',', '', $get('unit_price'));
                                $discount = $get('discount') === null ?  0 : (float)$get('discount');
                                $set('total', number_format(($count * $unitPrice) - (($count * $unitPrice) * $discount) / 100,2));
                            })->default(0)->required()->label('Discount'),
                            Forms\Components\TextInput::make('total')->live()->readOnly()->default(0)->required()->label('Total'),
                        ])->columnSpanFull()->columns(7),
                    ])->columns(2),
                    Forms\Components\Wizard\Step::make('journal')->label('Journal Entry')->schema([

                        Group::make()->relationship('invoice')->schema([

                            Forms\Components\Hidden::make('company_id')->default(getCompany()->id)->required(),

                            Forms\Components\Section::make([
                                Forms\Components\TextInput::make('number')
                                    ->columnSpan(1)
                                    ->default(getCompany()->financialPeriods()->where('status', "During")?->first()?->invoices()?->get()->last()?->number != null ? getCompany()->financialPeriods()->where('status', "During")->first()->invoices()->get()->last()->number + 1 : 1)->label('Voucher Number')->required()->maxLength(255)->readOnly(),
                                Forms\Components\TextInput::make('name')
                                    ->columnSpan(3)
                                    ->label('Voucher Title')->required()->maxLength(255),
                                Forms\Components\TextInput::make('reference')
                                    ->columnSpan(1)
                                    ->maxLength(255),
                                Forms\Components\DateTimePicker::make('date')
                                    ->columnSpan(2)
                                    ->required()->default(now()),
                                Forms\Components\FileUpload::make('document')->placeholder('Browse')->extraInputAttributes(['style' => 'height:30px!important;'])
                                    ->nullable(),
                                Placeholder::make('total :')->live()->content(function (Get $get) {
                                    if ($get->getData()['items']) {
                                        $produtTotal = array_map(function ($item) {
                                            // dd($item);
                                            try {
                                                //code...
                                                // return (($item['quantity'] * str_replace(',', '', $item['unit_price'])) + (($item['quantity'] * str_replace(',', '', $item['unit_price']) * $item['taxes']) / 100,2) + (($item['quantity'] * str_replace(',', '', $item['unit_price']) * $item['freights']) / 100,2));
                                                return (($item['quantity'] * str_replace(',', '', $item['unit_price'])) - (($item['quantity'] * str_replace(',', '', $item['unit_price'])) * $item['discount']) / 100);
                                            } catch (\Throwable $th) {
                                                //throw $th;
                                                return null;
                                            }
                                        }, $get->getData()['items']);

                                        return  collect($produtTotal)->sum() ? number_format(collect($produtTotal)->sum(),2) : '?';
                                    }
                                })->inlineLabel()
                            ])->columns(8),

                            Forms\Components\Section::make([
                                Forms\Components\Repeater::make('transactions')->label('')->relationship('transactions')->schema([
                                    SelectTree::make('account_id')->defaultOpenLevel(3)->live()->label('Account')->required()->relationship('Account', 'name', 'parent_id', modifyQueryUsing: fn($query) => $query->where('level', '!=', 'control')->where('company_id', getCompany()->id))->searchable(),
                                    Forms\Components\TextInput::make('description')->required(),

                                    Forms\Components\Hidden::make('company_id')->default(getCompany()->id)->required(),
                                    Forms\Components\TextInput::make('debtor')->default(0)->mask(RawJs::make('$money($input)'))->stripCharacters(',')->readOnly(fn(Get $get) => $get->getData()['type'] !== "1")
                                        ->rules([
                                            fn(Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {

                                                if ($get->getData()['type'] === "1") {


                                                    if ($get('debtor') == 0) {
                                                        $fail('The debtor field must be not zero.');
                                                    } else {

                                                        // dd(()));
                                                        $produtTotal = array_map(function ($item) {
                                                            // dd($item);
                                                            return (($item['quantity'] * str_replace(',', '', $item['unit_price'])) - (($item['quantity'] * str_replace(',', '', $item['unit_price'])) * $item['discount']) / 100);
                                                        }, $get->getData()['items']);

                                                        $invoiceTotal = array_map(function ($item) {
                                                            // dd($item);
                                                            return (str_replace(',', '', $item['debtor']));
                                                        }, $get->getData()['invoice']['transactions']);

                                                        $productSum = collect($produtTotal)->sum();
                                                        $invoiceSum = collect($invoiceTotal)->sum();

                                                        if ($invoiceSum != $productSum) {
                                                            $remainingAmount = $productSum - $invoiceSum;
                                                            $fail("The paid amount does not match the total price. Total amount:" . number_format($productSum,2) . ", Remaining amount: " . number_format($remainingAmount,2));
                                                        }
                                                    }
                                                } elseif ($get('debtor') != 0) {
                                                    $fail('The debtor field must be zero.');
                                                }
                                            },
                                        ]),
                                    Forms\Components\TextInput::make('creditor')->default(0)->mask(RawJs::make('$money($input)'))->stripCharacters(',')->readOnly(fn(Get $get) => $get->getData()['type'] === "1")
                                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                                            $set('cheque.amount', $state);
                                        })
                                        ->mask(RawJs::make('$money($input)'))->stripCharacters(',')
                                        ->mask(RawJs::make('$money($input)'))->stripCharacters(',')
                                        ->suffixIcon('cash')->suffixIconColor('success')->required()->default(0)->minValue(0)
                                        ->rules([
                                            fn(Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {

                                                if ($get->getData()['type'] !== "1") {


                                                    if ($get('creditor') == 0) {
                                                        $fail('The creditor field must be not zero.');
                                                    } else {

                                                        // dd(()));
                                                        $produtTotal = array_map(function ($item) {
                                                            // dd($item);
                                                            return (($item['quantity'] * str_replace(',', '', $item['unit_price'])) - (($item['quantity'] * str_replace(',', '', $item['unit_price'])) * $item['discount']) / 100);
                                                        }, $get->getData()['items']);

                                                        $invoiceTotal = array_map(function ($item) {
                                                            // dd($item);
                                                            return (str_replace(',', '', $item['creditor']));
                                                        }, $get->getData()['invoice']['transactions']);

                                                        $productSum = collect($produtTotal)->sum();
                                                        $invoiceSum = collect($invoiceTotal)->sum();

                                                        if ($invoiceSum != $productSum) {
                                                            $remainingAmount = $productSum - $invoiceSum;
                                                            $fail("The paid amount does not match the total price. Total amount:" . number_format($productSum,2) . ", Remaining amount: " . number_format($remainingAmount,2));
                                                        }
                                                    }
                                                } elseif ($get('creditor') != 0) {
                                                    $fail('The creditor field must be zero.');
                                                }
                                            },
                                        ]),
                                    Forms\Components\Checkbox::make('Cheque')->inline()->live(),
                                    Forms\Components\Section::make([
                                        Group::make()
                                            ->relationship('cheque')
                                            ->schema([
                                                Forms\Components\TextInput::make('cheque_number')->required()->maxLength(255),
                                                Forms\Components\TextInput::make('amount')->default(function (Get $get) {

                                                    if ($get('debtor') > 0) {
                                                        return $get('debtor');
                                                    }
                                                    if ($get('creditor') > 0) {
                                                        return $get('creditor');
                                                    }
                                                })->mask(RawJs::make('$money($input)'))->stripCharacters(',')->required()->numeric(),
                                                Forms\Components\DatePicker::make('issue_date')->required(),
                                                Forms\Components\DatePicker::make('due_date')->required(),
                                                Forms\Components\TextInput::make('payer_name')->required()->maxLength(255),
                                                Forms\Components\TextInput::make('payee_name')->required()->maxLength(255),
                                                Forms\Components\TextInput::make('bank_name')->maxLength(255),
                                                Forms\Components\TextInput::make('branch_name')->maxLength(255),
                                                Forms\Components\Textarea::make('description')->columnSpanFull(),
                                                Forms\Components\ToggleButtons::make('type')->options([0 => 'Receivable', 1 => 'Payable'])->inline()->grouped()->required(),
                                                Forms\Components\Hidden::make('company_id')->default(getCompany()->id)
                                            ])->columns(2),
                                    ])->collapsible()->persistCollapsed()->visible(fn(Forms\Get $get) => $get('Cheque')),
                                    Forms\Components\Hidden::make('financial_period_id')->required()->label('Financial Period')
                                        ->default(getPeriod()->id)
                                ])->columns(4)->defaultItems(1)
                                    ->mutateRelationshipDataBeforecreateUsing(function (array $data): array {
                                        $data['user_id'] = auth()->id();
                                        $data['company_id'] = getCompany()->id;
                                        $data['period_id'] = FinancialPeriod::query()->where('company_id', getCompany()->id)->where('status', "During")->first()->id;
                                        return $data;
                                    })
                            ])->columns(1)->columnSpanFull()

                        ])

                    ])
                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('party.name')->label('Vendor/Customer')
                    ->numeric()
                    ->sortable(),
                    Tables\Columns\TextColumn::make('account.name')->label('Expence/Icnome')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('from')
                    ->searchable(),
                Tables\Columns\TextColumn::make('to')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                ->state(fn($record)=>$record->type == "1" ? "Income" : "Expense")
                ->badge()->color(fn($record)=>$record->type == "1" ? "success" : "danger"),
                Tables\Columns\TextColumn::make('total')
                ->state(fn($record) => number_format($record->items->map(fn($item) => (($item['quantity'] * str_replace(',', '', $item['unit_price'])) - (($item['quantity'] * str_replace(',', '', $item['unit_price']) * $item['discount']) / 100) ))?->sum(),2))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Date')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
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
            'index' => Pages\ListFactors::route('/'),
            'create' => Pages\CreateFactor::route('/create'),
            'edit' => Pages\EditFactor::route('/{record}/edit'),
        ];
    }
}
