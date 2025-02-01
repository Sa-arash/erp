<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\BankResource\Pages\CreateCash;
use App\Filament\Admin\Resources\BankResource\Pages\EditCash;
use App\Filament\Admin\Resources\CashResource\Pages;
use App\Filament\Admin\Resources\CashResource\RelationManagers;
use App\Filament\Clusters\FinanceSettings;
use App\Models\Bank;
use App\Models\Cash;
use App\Models\FinancialPeriod;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CashResource extends Resource
{
    protected static ?string $model = Bank::class;
    protected static ?string $label='Cash';
    protected static ?string $pluralLabel='Cash';
    protected static ?string $cluster = FinanceSettings::class;

    protected static ?string $navigationIcon = 'heroicon-s-banknotes';
    protected static ?int $navigationSort=1;
    protected static ?string $navigationGroup = 'Finance Management';



    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }
    public static function getCluster(): ?string
    {
        $period = FinancialPeriod::query()->where('company_id', getCompanyUrl())->where('status', 'During')->first();
        if ($period) {
            return parent::getCluster();
        }
        return '';
    }

    public static function table(Table $table): Table
    {
        return $table->query(Bank::query()->where('company_id',getCompany()->id)->where('type',1))
            ->columns([
                Tables\Columns\TextColumn::make('bank_name')->label('Cash Name')
                    ->state(fn($record)=>$record->name."\n".$record->account->code)
                    ->searchable(),
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
            'index' => Pages\ListCashes::route('/'),
            'create' => CreateCash::route('/create-cash'),
            'edit' => EditCash::route('/{record}/edit'),
        ];
    }
}
