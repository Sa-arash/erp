<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\BankResource\Pages;
use App\Filament\Admin\Resources\BankResource\RelationManagers;
use App\Filament\Clusters\FinanceSettings;
use App\Models\Account;
use App\Models\Bank;
use App\Models\Currency;
use App\Models\FinancialPeriod;
use App\Models\Parties;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BankResource extends Resource
{
    protected static ?string $model = Bank::class;
    protected static ?string $cluster = FinanceSettings::class;
    protected static ?string $label='Bank';
    protected static ?string $navigationIcon = 'heroicon-o-building-library';
    protected static ?int $navigationSort=1;
    protected static ?string $navigationGroup = 'Finance Management';
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
        return $form
            ->schema([
                Forms\Components\TextInput::make('bank_name')->live(true)->afterStateUpdated(function (Forms\Set $set,$state){
                    $set('account.name',$state);
                })->required()->maxLength(255),
                Forms\Components\TextInput::make('branch_name')->maxLength(255),
                Forms\Components\TextInput::make('account_number')->required()->maxLength(255),
                Forms\Components\TextInput::make('account_code')->default(function () {
                    if (Bank::query()->where('company_id', getCompany()->id)->where('type',0)->latest()->first()) {
                        return generateNextCode(Bank::query()->where('company_id', getCompany()->id)->latest()->first()->account_code);
                    } else {
                        return "001";
                    }
                })->prefix(fn() =>  Account::query()->firstWhere('id', getCompany()->account_bank)?->code)->required()->maxLength(255),
                Forms\Components\TextInput::make('account_holder')->required()->maxLength(255),
                Forms\Components\TextInput::make('account_type')->maxLength(255),
                Forms\Components\Section::make([
                    Select::make('currency_id')->label('Currency')->default(getCompany()->currencies->where('is_company_currency',1)->first()?->id)->required()->options(getCompany()->currencies->pluck('name','id'))->searchable()->createOptionForm([
                        Section::make([
                            TextInput::make('name')->required()->maxLength(255),
                            TextInput::make('symbol')->required()->maxLength(255),
                            TextInput::make('exchange_rate')->required()->numeric(),
                        ])->columns(3)
                    ])->createOptionUsing(function ($data){
                        $data['company_id']=getCompany()->id;
                        Notification::make('success')->title('success')->success()->send();
                        return  Currency::query()->create($data)->getKey();
                    }),
                    Forms\Components\TextInput::make('iban')->maxLength(255),
                    Forms\Components\TextInput::make('swift_code')->maxLength(255),
                ])->columns(3),
                Forms\Components\Textarea::make('description')->columnSpanFull(),
                Fieldset::make('Account')->visible(fn($state)=>isset($state['id']))->relationship('account')->schema([
                    TextInput::make('name')->required()->maxLength(255),
                    SelectTree::make('parent_id')->required()->live()->label('Parent')->disabledOptions(function ($state, SelectTree $component) {
                        return Account::query()->where('level', 'detail')->orWhereHas('transactions',function ($query){})->pluck('id')->toArray();
                    })->defaultOpenLevel(3)->searchable()->enableBranchNode()->relationship('Account', 'name', 'parent_id', modifyQueryUsing: fn($query) => $query->where('stamp', "Assets")->where('company_id', getCompany()->id))
                        ->afterStateUpdated(function ($state, callable $set) {
                            $set('type', Account::query()->firstWhere('id', $state)->type);
                        }),
                    TextInput::make('code')->unique('accounts','code',ignoreRecord: true)->required()->maxLength(255),
                    ToggleButtons::make('type')->disabled()->grouped()->inline()->options(['creditor' => 'Creditor', 'debtor' => 'Debtor'])->required(),
                    ToggleButtons::make('group')->disabled()->grouped()->options(['Asset'=>'Asset','Liabilitie'=>'Liabilitie','Equity'=>'Equity','Income'=>'Income','Expense'=>'Expense'])->inline(),
                    Textarea::make('description')->maxLength(255)->columnSpanFull(),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->query(Bank::query()->where('company_id',getCompany()->id)->where('type',0))->headerActions([
        ])
            ->columns([
                Tables\Columns\TextColumn::make('bank_name')->label('Bank')
                ->state(fn($record)=>$record->bank_name."\n".$record->account->code)
                ->searchable(),
                Tables\Columns\TextColumn::make('branch_name')->searchable(),
                Tables\Columns\TextColumn::make('account_number')->searchable(),
                Tables\Columns\TextColumn::make('account_holder')->searchable(),
                Tables\Columns\TextColumn::make('account_type')->searchable(),
                Tables\Columns\TextColumn::make('currency.symbol')->searchable(),
                Tables\Columns\TextColumn::make('Balance')
                ->state(fn($record)=> number_format($record->account->transactions->sum('debtor')-$record->account->transactions->sum('creditor')))
                ->Color(fn($state)=>$state>=0?'success':'danger')

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->hidden(fn($record)=>$record->account->transactions->count())->action(function ($record){
                    $record->account()->delete();
                    $record->delete();
                }),
                Tables\Actions\Action::make('print')
                ->label('')
                ->icon('heroicon-s-printer')
                ->url(
                function($record){
                    if (FinancialPeriod::query()->where('company_id', getCompany()->id)->first()){
                        return route('pdf.account', [
                            'period' => FinancialPeriod::query()->where('company_id', getCompany()->id)->first()?->id,
                            'account' =>$record->account->id,
                        ]);
                    }
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBanks::route('/'),
            'create' => Pages\CreateBank::route('/create'),
            'edit' => Pages\EditBank::route('/{record}/edit'),

        ];
    }
}
