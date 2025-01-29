<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\BankResource\Pages;
use App\Filament\Admin\Resources\BankResource\RelationManagers;
use App\Filament\Clusters\FinanceSettings;
use App\Models\Account;
use App\Models\Bank;
use App\Models\FinancialPeriod;
use App\Models\Parties;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BankResource extends Resource
{
    protected static ?string $model = Bank::class;
    protected static ?string $cluster = FinanceSettings::class;
    protected static ?string $label='Bank/Cash';
    protected static ?string $navigationIcon = 'heroicon-o-building-library';
    protected static ?int $navigationSort=1;
    protected static ?string $navigationGroup = 'Finance';
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
                Forms\Components\TextInput::make('bank_name')->required()->maxLength(255),
                Forms\Components\TextInput::make('branch_name')->maxLength(255),
                Forms\Components\TextInput::make('account_number')->required()->maxLength(255),
                Forms\Components\TextInput::make('account_code')->default(function () {
                    if (Bank::query()->where('company_id', getCompany()->id)->where('type',0)->latest()->first()) {
                        return generateNextCode(Bank::query()->where('company_id', getCompany()->id)->latest()->first()->account_code);
                    } else {
                        return "001";
                    }

                })->prefix(fn(Get $get) => Account::query()->firstWhere('id', getCompany()->account_bank)?->code)->required()->maxLength(255),
                Forms\Components\TextInput::make('account_holder')->required()->maxLength(255),
                Forms\Components\TextInput::make('account_type')->maxLength(255),
                Forms\Components\Section::make([
                    Forms\Components\Select::make('currency')->required()->required()->options(getCurrency())->searchable(),
                    Forms\Components\TextInput::make('iban')->maxLength(255),
                    Forms\Components\TextInput::make('swift_code')->maxLength(255),
                ])->columns(3),
                Forms\Components\Textarea::make('description')->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->headerActions([
        ])
            ->columns([
                Tables\Columns\TextColumn::make('bank_name')
                ->state(fn($record)=>$record->bank_name."\n".$record->account->code)
                ->searchable(),
                Tables\Columns\TextColumn::make('branch_name')->searchable(),
                Tables\Columns\TextColumn::make('account_number')->searchable(),
                Tables\Columns\TextColumn::make('account_holder')->searchable(),
                Tables\Columns\TextColumn::make('account_type')->searchable(),
                Tables\Columns\TextColumn::make('currency')->searchable(),
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
                ->url(fn($record) => route('pdf.account', [
                    'period' => FinancialPeriod::query()->where('company_id', getCompany()->id)?->first()->id,
                    'account' =>$record->account->id,
                ])),
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
            'createCash' => Pages\CreateCash::route('/create-cash'),
            'editCash' => Pages\EditCash::route('/{record}/edit-cash'),
        ];
    }
}
