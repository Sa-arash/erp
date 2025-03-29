<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AccountResource\Pages;
use App\Filament\Admin\Resources\AccountResource\RelationManagers;
use App\Filament\Clusters\FinanceSettings;
use App\Models\Account;
use App\Models\Currency;
use App\Models\FinancialPeriod;
use App\Models\Transaction;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\Rules\Unique;

class AccountResource extends Resource
{
    protected static ?string $model = Account::class;
    protected static ?int $navigationSort = 0;
    protected static ?string $cluster = FinanceSettings::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationGroup = 'Finance Management';
    protected static ?string $label = 'Finance/Chart Of Account';
    protected static ?string $pluralLabel = 'Chart Of Account';



    public static function getCluster(): ?string
    {
        $period = FinancialPeriod::query()->where('company_id', getCompanyUrl())->where('status', 'During')->first();
        if ($period) {
            return parent::getCluster();
        }
        return '';
    }


    public static function form(Form $form): Form
    {

        // 'group',['Asset','Liabilitie','Equity','Income','Expense']
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required()->maxLength(255),
                SelectTree::make('parent_id')
                ->required()
                ->label('Parent')->disabledOptions(function () {
                    return Account::query()->where('level', 'detail')->orWhereHas('transactions',function ($query){})->pluck('id')->toArray();
                })->defaultOpenLevel(1)->enableBranchNode()->relationship('account', 'name', 'parent_id', modifyQueryUsing: fn($query) => $query->where('level', '!=', 'detail')->where('company_id', getCompany()->id), modifyChildQueryUsing: fn($query) => $query->where('company_id', getCompany()->id))
                    ->afterStateUpdated(function ($state, callable $set) {
                        $account = Account::query()->where('parent_id', $state)->orderBy('id', 'desc')->first();
                        $parent = Account::query()->where('id', $state)->first();
                        if ($account) {
                            $set('code', generateNextCode(str_replace($parent->code, '', $account->code)));
                        } else {
                            $set('code', "001");
                        }
                        $set('type', $parent?->type);
                        $set('group', $parent?->group);
                         $set('has_cheque', $parent?->has_cheque);
                    })->searchable()->live()->default((int)Request::query('parent')),
                Forms\Components\TextInput::make('code')->formatStateUsing(function ($state) {
                    if ((int)Request::query('parent')) {
                        $parent = Account::query()->where('id',  (int)Request::query('parent'))->first();
                        $account = Account::query()->where('parent_id', $parent->id)->orderBy('id', 'desc')->first();
                        if ($account) {
                            return  generateNextCode(str_replace($parent->code, '', $account->code));
                        } else {
                            return "001";
                        }
                    }
                    return  $state;
                })->live()->unique(ignoreRecord: true,modifyRuleUsing: function (Unique $rule, $state, Get $get) {
                    $parent = Account::query()->firstWhere('id', $get('parent_id'));
                    return $rule->where('code', $parent . $state)->where('company_id', getCompany()->id);
                })->required()->maxLength(255)->prefix(fn(Get $get) => Account::query()->firstWhere('id', $get('parent_id'))?->code),
                Select::make('currency_id')->live()->label('Currency')->required()->relationship('currency', 'name', modifyQueryUsing: fn($query) => $query->where('company_id', getCompany()->id))->searchable()->preload()->createOptionForm([
                    \Filament\Forms\Components\Section::make([
                        TextInput::make('name')->required()->maxLength(255),
                        TextInput::make('symbol')->required()->maxLength(255),
                        TextInput::make('exchange_rate')->required()->numeric()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
                    ])->columns(3)
                ])->createOptionUsing(function ($data) {
                    $data['company_id'] = getCompany()->id;
                    Notification::make('success')->title('success')->success()->send();
                    return Currency::query()->create($data)->getKey();
                })->editOptionForm([
                    Section::make([
                        TextInput::make('name')->required()->maxLength(255),
                        TextInput::make('symbol')->required()->maxLength(255),
                        TextInput::make('exchange_rate')->required()->numeric()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
                    ])->columns(3)
                ])->afterStateUpdated(function ($state, Forms\Set $set) {
                    $currency = Currency::query()->firstWhere('id', $state);
                    if ($currency) {
                        $set('exchange_rate', $currency->exchange_rate);
                    }
                })->editOptionAction(function ($state, Forms\Set $set) {
                    $currency = Currency::query()->firstWhere('id', $state);
                    if ($currency) {
                        $set('exchange_rate', $currency->exchange_rate);
                    }
                }),
                Forms\Components\ToggleButtons::make('type')->disabled()->formatStateUsing(function ($state) {
                    if ((int)Request::query('parent')){
                        return Account::query()->where('id', (int)Request::query('parent'))->first()?->type;
                    }else{
                        return  $state;
                    }
                })->grouped()->inline()->options(['creditor' => 'Creditor', 'debtor' => 'Debtor'])->required(),
                Forms\Components\ToggleButtons::make('group')->disabled()->grouped()
                ->formatStateUsing(function ($state) {
                    if ((int)Request::query('parent')){
                        return Account::query()->where('id', (int)Request::query('parent'))->first()?->group;
                    }else{
                        return  $state;
                    }
                })
                ->options(['Asset'=>'Asset','Liabilitie'=>'Liabilitie','Equity'=>'Equity','Income'=>'Income','Expense'=>'Expense'])->inline(),
                Toggle::make('has_cheque')->required()->disabled()->inline()->formatStateUsing(function ($state) {
                    if ((int)Request::query('parent')){
                        return Account::query()->where('id', (int)Request::query('parent'))->first()?->has_cheque;
                    }else{
                        return  $state;
                    }
                }),
                Forms\Components\Textarea::make('description')->maxLength(255)->columnSpanFull(),

            ]);
    }

    public static function table(Table $table): Table
    {

        return $table->defaultSort('code')
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('name')->state(function ($record) {
                    if ($record->level === "main") {
                        return  $record->name;
                    }
                    elseif ($record->level === "general") {
                        return "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $record->name;
                    } elseif ($record->level === "detail") {
                        return "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $record->name;
                    } elseif ($record->level === "group") {
                        return "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$record->name;
                    } else {
                        return  "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $record->name;
                    }
                })->html()->color(function ($record) {
                    if ($record->level === "general") {
                        return "info";
                    } elseif ($record->level === "detail") {
                        return "success";
                    } elseif ($record->level === "group") {
                        return "secondary";
                    } else {
                        return  'warning';
                    }
                })->searchable(),
                Tables\Columns\TextColumn::make('code')->extraAttributes(['style' => "letter-spacing:1px"])->copyable()->searchable(),
                Tables\Columns\TextColumn::make('level')->searchable()->toggleable()->toggledHiddenByDefault(),
                Tables\Columns\TextColumn::make('group')->searchable(),
                Tables\Columns\TextColumn::make('type')->searchable()->toggleable()->toggledHiddenByDefault(),
            ])
            ->filters([
                //
            ])
            ->actions([

                Tables\Actions\EditAction::make()->hidden(fn($record) => (bool)$record->built_in),
                Tables\Actions\DeleteAction::make()->action(function ($record) {
                    $record->forceDelete();
                })->hidden(fn($record) => $record->built_in === 1 or $record->transactions->count() > 0),
                Tables\Actions\ViewAction::make()->infolist(function () {
                    return [
                        Section::make([
                            TextEntry::make('name'),
                            TextEntry::make('parent.name'),
                            TextEntry::make('code'),
                            TextEntry::make('level'),
                            TextEntry::make('type')->color('info')->badge(),
                        ])->columns(2),
                        Section::make([
                            TextEntry::make('debtor')->label('Total Debtor')->state(function ($record) {
                                $id = $record->id;
                                $accounts = Account::query()
                                    ->where('id', $id)
                                    ->orWhere('parent_id', $id)
                                    ->orWhereHas('account', function ($query) use ($id) {
                                        $query->where('parent_id', $id)
                                            ->orWhereHas('account', function ($query) use ($id) {
                                                $query->where('parent_id', $id);
                                            });
                                    })
                                    ->pluck('id');

                                $totalDebtor = Transaction::query()
                                    ->whereIn('account_id', $accounts)
                                    ->where('financial_period_id', getPeriod()?->id)
                                    ->sum('debtor');

                                return $totalDebtor;
                            })->numeric(),
                            TextEntry::make('creditor')->state(function ($record) {
                                $id = $record->id;
                                $accounts = Account::query()
                                    ->where('id', $id)
                                    ->orWhere('parent_id', $id)
                                    ->orWhereHas('account', function ($query) use ($id) {
                                        $query->where('parent_id', $id)
                                            ->orWhereHas('account', function ($query) use ($id) {
                                                $query->where('parent_id', $id);
                                            });
                                    })
                                    ->pluck('id');

                                $totalDebtor = Transaction::query()
                                    ->whereIn('account_id', $accounts)
                                    ->where('financial_period_id', getPeriod()?->id)
                                    ->sum('creditor');

                                return $totalDebtor;
                            })->numeric(),
                            TextEntry::make('balance')->state(function ($record) {
                                $id = $record->id;
                                $accounts = Account::query()
                                    ->where('id', $id)
                                    ->orWhere('parent_id', $id)
                                    ->orWhereHas('account', function ($query) use ($id) {
                                        $query->where('parent_id', $id)
                                            ->orWhereHas('account', function ($query) use ($id) {
                                                $query->where('parent_id', $id);
                                            });
                                    })->get();

                                if ($record->type == 'debtor') {
                                    return $accounts->map(fn($item) => $item->transactions->where('financial_period_id', getPeriod()?->id)
                                        ->sum('debtor')-
                                        $item->transactions->where('financial_period_id', getPeriod()?->id)->sum('creditor'))->sum();
                                } elseif ($record->type == 'creditor') {
                                    return $accounts->map(fn($item) => $item->transactions->where('financial_period_id', getPeriod()?->id)
                                        ->sum('creditor')-
                                        $item->transactions->where('financial_period_id', getPeriod()?->id)->sum('debtor'))->sum();
                                }
                            })->numeric(),
                        ])->columns(3)
                    ];
                }),
                ActionGroup::make([
                    Action::make('Report')
                        ->url(function ($record) {
                            $financial = getPeriod();
                            if ($financial) {
                                return route('pdf.account', [
                                    'period' => FinancialPeriod::query()->where('status', 'During')->where('company_id', getCompany()->id)->first()->id,
                                    'account' => $record->id,
                                ]);
                            }
                        })->icon('heroicon-s-printer')->color('primary'),
                    Action::make('CurrencyReport')->label('CurrencyReport')
                        ->url(function ($record) {
                            $financial = getPeriod();
                            if ($financial) {
                                return route('pdf.accountCurrency', [
                                    'period' => FinancialPeriod::query()->where('status', 'During')->where('company_id', getCompany()->id)->first()->id,
                                    'account' => $record->id,
                                ]);
                            }
                        })->icon('heroicon-s-printer')->color('warning')->visible(fn($record)=>$record->currency_id !== defaultCurrency()?->id),
                    Action::make('new')->icon('heroicon-o-document-chart-bar')->color('info')->label('New Account')->hidden(fn($record) => $record->level === "detail" || isset($record->transactions[0]))
                        ->url(function ($record) {
                            return AccountResource::getUrl('create', ['parent' => $record->id]);
                        })
                    //   fn($record)=> route('pdf.account', [
                    //     // 'period' => FinancialPeriod::query()->where('company_id', getCompany()->id),
                    //     // 'account' => $record->id,
                    //   ]))



                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //                    Tables\Actions\DeleteBulkAction::make()->action(function ($records){
                    //                        dd($records);
                    //                    }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //TransactionsRelationManager::class
        ];
    }
    public static function getWidgets(): array
    {
        return [
            // AccountOverview::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccounts::route('/'),
            'create' => Pages\CreateAccount::route('/create'),
            'edit' => Pages\EditAccount::route('/{record}/edit'),
        ];
    }
}
