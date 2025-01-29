<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\FinancialPeriodResource\Pages;
use App\Filament\Admin\Resources\FinancialPeriodResource\RelationManagers;
use App\Filament\Clusters\FinanceSettings;
use App\Models\Account;
use App\Models\Bank;
use App\Models\Cheque;
use App\Models\FinancialPeriod;
use App\Models\Invoice;
use App\Models\Parties;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FinancialPeriodResource extends Resource
{
    protected static ?string $model = FinancialPeriod::class;
    protected static ?string $cluster = FinanceSettings::class;

    protected static ?string $label='Financial Period';
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationGroup = 'Finance Management';

    public static function getCluster(): ?string
    {
        $period = FinancialPeriod::query()->where('company_id', getCompanyUrl())->where('status', 'During')->first();
        if ($period) {
            return parent::getCluster();
        }
        return '';
    }

    public static function canCreate(): bool
    {
        return getCompany()->financialPeriods->firstWhere('status','During')===null ;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->columnSpanFull()->required()->maxLength(255),
//                Forms\Components\ToggleButtons::make('is_active')
//                ->unique(modifyRuleUsing: function (Unique $rule) {
//                    return $rule->where('is_active', 1)->where('company_id',getCompany()->id);
//                })
//                ->label('Status')->inline()->grouped()->boolean('Active','UnActive')->required(),
                //  Forms\Components\ToggleButtons::make('status')->options(['Before'=>'Before','During'=>'During','End'=>'End'])->inline()->grouped(),
                Forms\Components\DatePicker::make('start_date')->default(now()->startOfYear())->required(),
                Forms\Components\DatePicker::make('end_date')->default(now()->startOfYear()->addYear())->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->headerActions([
            Tables\Actions\Action::make('clear')->requiresConfirmation()->action(function (){
                FinancialPeriod::query()->where('company_id',getCompany()->id)->delete();
                Invoice::query()->where('company_id',getCompany()->id)->forceDelete();
                Bank::query()->where('company_id',getCompany()->id)->delete();
                Parties::query()->where('company_id',getCompany()->id)->delete();
                Account::query()->where('company_id',getCompany()->id)->whereNot('built_in',1)->forceDelete();
                Cheque::query()->where('company_id',getCompany()->id)->delete();
                $url = "admin/" . getCompany()->id . "/financial-periods";
                return redirect($url);

            })
        ])
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('start_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('end_date')->date()->sortable(),
//                Tables\Columns\TextColumn::make('Balance Period')->label('Balance Period')->state(function ($record) {
//                    if ($record->status === "Before") {
//                        return "Initial Journal Entry";
//                    }elseif ($record->status === "End"){
//                        return "End";
//                    }
//                    return "";
//                })->color('aColor')->url(fn($record) => FinancialPeriodResource::getUrl('balance_period', ['record' => $record->id]))->sortable(),
                Tables\Columns\TextColumn::make('status')->alignCenter()->badge(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('balance_period')->label('Initial Journal Entry')->visible(fn($record)=>$record->status->name==="Before")->url(fn($record) => FinancialPeriodResource::getUrl('balance_period', ['record' => $record->id])),
                Tables\Actions\EditAction::make()->visible(fn($record)=>$record->status->name !== 'End'),
                Tables\Actions\Action::make('The End')->requiresConfirmation()
                ->icon('heroicon-o-check') // اضافه کردن آیکون
                ->color('danger')
                ->action(function($record){
                    $record->update(['status' => 'End']);

                               $url = "admin/" . getCompany()->id . "/financial-periods";
                               return redirect($url);

                })->visible(fn($record)=>$record->status->name === 'During'),
//                $record->update($data);
//        if ($data['status'] == "Before") {
//            $url = "admin/" . getCompany()->id . "/financial-periods";
//            return redirect($url);
//        } else {
//            $url = "admin/" . getCompany()->id . "/finance-settings/financial-periods";
//            return redirect($url);
//        }
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
            'index' => Pages\ListFinancialPeriods::route('/'),
            'balance_period' => Pages\BalancePeriod::route('balance-period/{record}')
        ];
    }
}
