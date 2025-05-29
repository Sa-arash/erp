<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PartiesResource\Pages;
use App\Filament\Admin\Resources\PartiesResource\RelationManagers;
use App\Models\Account;
use App\Models\Currency;
use App\Models\FinancialPeriod;
use App\Models\Parties;
use App\Models\Transaction;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Infolists\Components\Section;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class PartiesResource extends Resource
{
    protected static ?string $model = Parties::class;
    protected static ?string $label = 'Customer/Vendor';
    protected static ?string $navigationIcon = 'heroicon-s-user-group';

    protected static ?int $navigationSort = 1;
    protected static ?string $navigationGroup = 'Finance Management';
    protected static ?string $navigationLabel =  'Customers/Vendors';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make([
                    Forms\Components\TextInput::make('name')->label('Company/Name')->required()->maxLength(255),
                    Forms\Components\TextInput::make('phone')->tel()->maxLength(255),
                    Forms\Components\TextInput::make('email')->email()->maxLength(255),
                    Forms\Components\Textarea::make('address')->columnSpanFull(),
                ])->columns(3),
                Forms\Components\ToggleButtons::make('type')->visible(fn($operation) => $operation === "create")->live()->grouped()->options(['vendor' => 'Vendor', 'customer' => 'Customer', 'both' => 'Both'])->inline()->required(),
                getSelectCurrency(),
                SelectTree::make('parent_vendor')->visible(function (Forms\Get $get) {

                    if ($get('type') == "both") {
                        if ($get("account_vendor") === null) {
                            return true;
                        }
                    } elseif ($get('type') == "vendor") {
                        if ($get("account_vendor") === null) {
                            return true;
                        }
                    } else {
                        return false;
                    }
                })->disabledOptions(function () {
                    return Account::query()->where('level', 'detail')->where('company_id', getCompany()->id)->orWhereHas('transactions', function ($query) {})->pluck('id')->toArray();
                })->hidden(fn($operation) => (bool)$operation === "edit")->default(getCompany()?->vendor_account)->enableBranchNode()->model(Transaction::class)->defaultOpenLevel(3)->live()->label('Parent Vendor Account')->required()->relationship('Account', 'name', 'parent_id', modifyQueryUsing: fn($query) => $query->where('stamp', "Liabilities")->where('company_id', getCompany()->id)),

                SelectTree::make('parent_customer')->visible(function (Forms\Get $get) {
                    if ($get('type') == "both") {
                        if ($get("account_customer") === null) {
                            return true;
                        }
                    } elseif ($get('type') == "customer") {
                        if ($get("account_customer") === null) {
                            return true;
                        }
                    } else {
                        return false;
                    }
                })->default(getCompany()?->customer_account)->disabledOptions(function ($state, SelectTree $component) {
                    return Account::query()->where('level', 'detail')->where('company_id', getCompany()->id)->orWhereHas('transactions', function ($query) {})->pluck('id')->toArray();
                })->enableBranchNode()->model(Transaction::class)->defaultOpenLevel(3)->live()->label('Parent Customer Account')->required()->relationship('Account', 'name', 'parent_id', modifyQueryUsing: fn($query) => $query->where('stamp', "Assets")->where('company_id', getCompany()->id)),
                Forms\Components\TextInput::make('account_code_vendor')
                    ->prefix(fn(Get $get) => Account::find($get('parent_vendor'))?->code)
                    ->default(function () {
                        if (Parties::query()->where('company_id', getCompany()->id)->where('type', 'vendor')->latest()->first()) {
                            return generateNextCode(Parties::query()->where('company_id', getCompany()->id)->where('type', 'vendor')->latest()->first()->account_code_vendor);
                        } else {
                            return "001";
                        }
                    })->unique('accounts', 'code', ignoreRecord: true)->visible(function (Forms\Get $get) {

                        if ($get('type') == "both") {
                            if ($get("account_vendor") === null) {
                                return true;
                            }
                        } elseif ($get('type') == "vendor") {
                            if ($get("account_vendor") === null) {
                                return true;
                            }
                        } else {
                            return false;
                        }
                    })->required()->tel()->maxLength(255),
                Forms\Components\TextInput::make('account_code_customer')->unique('accounts', 'code', ignoreRecord: true)
                    ->prefix(fn(Get $get) => Account::find($get('parent_customer'))?->code)
                    ->default(function () {
                        if (Parties::query()->where('company_id', getCompany()->id)->where('type', 'customer')->latest()->first()) {
                            return generateNextCode(Parties::query()->where('company_id', getCompany()->id)->where('type', 'customer')->latest()->first()->account_code_customer);
                        } else {
                            return "001";
                        }
                    })->visible(function (Forms\Get $get) {
                        if ($get('type') === "both") {
                            if ($get("account_customer") === null) {
                                return true;
                            }
                        } elseif ($get('type') === "customer") {
                            if ($get("account_customer") === null) {
                                return true;
                            }
                        } else {
                            return false;
                        }
                    })->required()->tel()->maxLength(255),
                Forms\Components\Fieldset::make('Account Vendor')->visible(fn($state) => isset($state['id']))->relationship('accountVendor')->schema([
                    Forms\Components\TextInput::make('name')->required()->maxLength(255),
                    SelectTree::make('parent_id')->live()->label('Parent')->disabledOptions(function ($state, SelectTree $component) {
                        return Account::query()->where('level', 'detail')->orWhereHas('transactions', function ($query) {})->pluck('id')->toArray();
                    })->defaultOpenLevel(1)->searchable()->enableBranchNode()->relationship('Account', 'name', 'parent_id', modifyQueryUsing: fn($query) => $query->where('stamp', "Liabilities")->where('company_id', getCompany()->id))
                        ->afterStateUpdated(function ($state, callable $set) {
                            $set('type', Account::query()->firstWhere('id', $state)->type);
                        }),
                    Forms\Components\TextInput::make('code')->unique('accounts', 'code', ignoreRecord: true)->required()->maxLength(255),
                    ToggleButtons::make('type')->disabled()->grouped()->inline()->options(['creditor' => 'Creditor', 'debtor' => 'Debtor'])->required(),
                    ToggleButtons::make('group')->disabled()->grouped()->options(['Asset' => 'Asset', 'Liabilitie' => 'Liabilitie', 'Equity' => 'Equity', 'Income' => 'Income', 'Expense' => 'Expense'])->inline(),
                    Forms\Components\Textarea::make('description')->maxLength(255)->columnSpanFull(),
                ]),
                Forms\Components\Fieldset::make('Account Customer')->visible(fn($state) => isset($state['id']))->relationship('accountCustomer')->schema([
                    Forms\Components\TextInput::make('name')->required()->maxLength(255),
                    SelectTree::make('parent_id')->live()->label('Parent')->disabledOptions(function ($state, SelectTree $component) {
                        return Account::query()->where('level', 'detail')->orWhereHas('transactions', function ($query) {})->pluck('id')->toArray();
                    })->defaultOpenLevel(1)->searchable()->enableBranchNode()->relationship('Account', 'name', 'parent_id', modifyQueryUsing: fn($query) => $query->where('stamp', "Assets")->where('company_id', getCompany()->id))
                        ->afterStateUpdated(function ($state, callable $set) {
                            $set('type', Account::query()->firstWhere('id', $state)->type);
                        }),
                    Forms\Components\TextInput::make('code')->unique('accounts', 'code', ignoreRecord: true)->required()->maxLength(255),
                    ToggleButtons::make('type')->disabled()->grouped()->inline()->options(['creditor' => 'Creditor', 'debtor' => 'Debtor'])->required(),
                    ToggleButtons::make('group')->disabled()->grouped()->options(['Asset' => 'Asset', 'Liabilitie' => 'Liabilitie', 'Equity' => 'Equity', 'Income' => 'Income', 'Expense' => 'Expense'])->inline(),
                    Forms\Components\Textarea::make('description')->maxLength(255)->columnSpanFull(),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')->headerActions([
                ExportAction::make()
                    ->after(function () {
                        if (Auth::check()) {
                            activity()
                                ->causedBy(Auth::user())
                                ->withProperties([
                                    'action' => 'export',
                                ])
                                ->log('Export' . "Customers/Vendors");
                        }
                    })->exports([
                        ExcelExport::make()->askForFilename("Customers&Vendors")->withColumns([
                            Column::make('name'),
                            Column::make('id')->formatStateUsing(function ($record) {
                                if ($record->type === "both") {
                                    return $record->accountVendor?->code . " - " . $record->accountCustomer?->code;
                                } elseif ($record->type === "vendor") {
                                    return $record->accountVendor?->code;
                                } else {
                                    return $record->accountCustomer?->code;
                                }
                            })->heading('Account Code'),
                            Column::make('type'),
                            Column::make('phone'),

                            Column::make('email'),
                            Column::make('company_id')->heading("balance")
                                ->formatStateUsing(
                                    function ($record) {
                                        if ($record->type === 'customer') {
                                            return '' . number_format($record->accountCustomer->transactions->sum('debtor') - $record->accountCustomer->transactions->sum('creditor'));
                                        } elseif ($record->type === 'vendor') {
                                            return $record->accountVendor->transactions->sum('creditor') - $record->accountVendor->transactions->sum('debtor');
                                        } elseif ($record->type === 'both') {
                                            return ($record->accountVendor->transactions->sum('creditor') - $record->accountVendor->transactions->sum('debtor'))
                                                -
                                                ($record->accountCustomer->transactions->sum('debtor') - $record->accountCustomer->transactions->sum('creditor'));
                                        }
                                    }
                                ),

                        ]),
                    ])->label('Export Customers/Vendors')->color('purple')
            ])




            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('account.code')->state(function ($record) {
                    if ($record->type === "both") {
                        return $record->accountVendor?->code . " - " . $record->accountCustomer?->code;
                    } elseif ($record->type === "vendor") {
                        return $record->accountVendor?->code;
                    } else {
                        return $record->accountCustomer?->code;
                    }
                })->badge()->label('Account Code'),
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),

                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('balance')->badge()
                    ->state(
                        function ($record) {
                            if ($record->type === 'customer') {
                                return '' . number_format($record->accountCustomer->transactions->sum('debtor') - $record->accountCustomer->transactions->sum('creditor'));
                            } elseif ($record->type === 'vendor') {
                                return $record->accountVendor->transactions->sum('creditor') - $record->accountVendor->transactions->sum('debtor');
                            } elseif ($record->type === 'both') {
                                return ($record->accountVendor->transactions->sum('creditor') - $record->accountVendor->transactions->sum('debtor'))
                                    -
                                    ($record->accountCustomer->transactions->sum('debtor') - $record->accountCustomer->transactions->sum('creditor'));
                            }
                        }
                    )->Color(function ($record, $state) {

                        // => $record->type >= 0 ? 'success' : 'danger')
                    })
                    ->numeric(),

            ])
            ->filters([
                SelectFilter::make('type')->searchable()->preload()->options(['vendor' => 'Vendor', 'customer' => 'Customer', 'both' => 'Both']),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('print')->label('')->visible(function () {
                    return FinancialPeriod::query()->where('company_id', getCompany()->id)->first() !== null;
                })
                    ->icon('heroicon-s-printer')
                    ->url(function ($record) {
                        if (FinancialPeriod::query()->where('company_id', getCompany()->id)->first()) {
                            return  route('pdf.account', [
                                'period' => FinancialPeriod::query()->where('company_id', getCompany()->id)->first()?->id,
                                'account' => ($record->accountVendor?->id && $record->accountCustomer?->id)
                                    ? $record->accountVendor->id . "-" . $record->accountCustomer->id
                                    : ($record->accountVendor?->id ?? $record->accountCustomer?->id),
                            ]);
                        }
                    }),
                //                Tables\Actions\DeleteAction::make()->hidden(fn($record)=>$record->account->tra)


            ])
            ->bulkActions([
                ExportBulkAction::make()
                    ->after(function () {
                        if (Auth::check()) {
                            activity()
                                ->causedBy(Auth::user())
                                ->withProperties([
                                    'action' => 'export',
                                ])
                                ->log('Export' . "Customers/Vendors");
                        }
                    })->exports([
                        ExcelExport::make()->askForFilename("Customers&Vendors")->withColumns([
                            Column::make('name'),
                            Column::make('id')->formatStateUsing(function ($record) {
                                if ($record->type === "both") {
                                    return $record->accountVendor?->code . " - " . $record->accountCustomer?->code;
                                } elseif ($record->type === "vendor") {
                                    return $record->accountVendor?->code;
                                } else {
                                    return $record->accountCustomer?->code;
                                }
                            })->heading('Account Code'),
                            Column::make('type'),
                            Column::make('phone'),

                            Column::make('email'),
                            Column::make('company_id')->heading("balance")
                                ->formatStateUsing(
                                    function ($record) {
                                        if ($record->type === 'customer') {
                                            return '' . number_format($record->accountCustomer->transactions->sum('debtor') - $record->accountCustomer->transactions->sum('creditor'));
                                        } elseif ($record->type === 'vendor') {
                                            return $record->accountVendor->transactions->sum('creditor') - $record->accountVendor->transactions->sum('debtor');
                                        } elseif ($record->type === 'both') {
                                            return ($record->accountVendor->transactions->sum('creditor') - $record->accountVendor->transactions->sum('debtor'))
                                                -
                                                ($record->accountCustomer->transactions->sum('debtor') - $record->accountCustomer->transactions->sum('creditor'));
                                        }
                                    }
                                ),

                        ]),
                    ])->label('Export Customers/Vendors')->color('purple')
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
            'index' => Pages\ListParties::route('/'),
            'create' => Pages\CreateParties::route('/create'),
            'edit' => Pages\EditParties::route('/{record}/edit'),
        ];
    }
}
