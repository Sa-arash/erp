<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TransActionResource\Pages;
use App\Filament\Admin\Resources\TransActionResource\RelationManagers;
use App\Filament\Exports\TransactionExporter;
use App\Models\Account;
use App\Models\FinancialPeriod;
use App\Models\Invoice;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;

class TransActionResource extends Resource
{
    protected static ?string $model = Transaction::class;
    protected static ?string $label = 'Journal Report';
    protected static ?string $navigationGroup ="Accounting Report";
    protected static ?int $navigationSort = 0;

    public static function canCreate(): bool
    {
        return false;
    }
    public static function canAccess(): bool
    {
      return (getPeriod()  && (auth()->user()->can('view_trans::action')));
    }
//    public static function getNavigationUrl(): string
//    {
//        $period_id = getCompany()->financialPeriods->firstWhere('is_active', 1)?->id;
//        return $period_id ? static::getUrl() . "?tableFilters[invoice_id][value]=" . $period_id : static::getUrl();
//    }


    // public static function canAccess(): bool
    // {
    //     $period = FinancialPeriod::query()->where('company_id', getCompanyUrl())->where('status', 'During')->first();
    //     if ($period) {
    //         return true;
    //     }
    //     return false;
    // }



    protected static ?string $navigationIcon = 'payment';


    public static function table(Table $table): Table
    {
        return $table
            // ->defaultSort('payment_date', 'desc')
            ->headerActions([
                // Action::make('Filter')->form(function (Table $table) {
                //     if ($table->getLivewire()->activeTab === "General") {
                //         return [
                //             Select::make('account_id')->label('Account')->searchable()->preload()->options(Account::query()->where('company_id', getCompany()->id)->where('level', strtolower($table->getLivewire()->activeTab))->pluck('name', 'id'))->multiple()
                //         ];
                //     }else{
                //         return [
                //             Select::make('account_id')->label('Account')->searchable()->preload()->options(Account::query()->where('company_id', getCompany()->id)->where('level', strtolower($table->getLivewire()->activeTab))->pluck('name', 'id'))->multiple()
                //         ];
                //     }
                // })
                // // ->extraModalFooterActions(fn(array $data)=>[
                // //     Action::make('Filter')
                //     ->action(function (array $data,Table $table){
                //         // dd($data);
                //         $filter=[];
                //         $filter['activeTab']=$table->getLivewire()->activeTab;
                //         $i=0;

                //         foreach ($data['account_id'] as $datum){
                //             $filter['tableFilters[filter][account_id]['.$i.']']=$datum;
                //             $i++;
                //         }
                //         return redirect()->route('pdf.account',[
                //                         'period' =>  FinancialPeriod::query()->where('company_id', getCompanyUrl())->where('status', 'During')->first(),
                //                         'account' => implode('-',$data['account_id']),
                //                     ]);
                //         // return redirect(TransActionResource::getUrl('index',$filter));
                //     }),
                //     Action::make('print')
                //     ->action(function (array $data){
                //         dd($data);
                //         return redirect()->route('pdf.account',[
                //             'period' => $financialPeriod ?? ' ',
                //             'account' => implode('-',$data['account_id']),
                //         ]);
                //     }),
                // ]),

                // Tables\Actions\ExportAction::make()
                //     ->exporter(TransactionExporter::class)->color('purple'),
                // Action::make('Balance Report')->url(fn() => route('pdf.balance')),
                // Action::make('Journal Report')->url(fn() => route('pdf.jornal', ['period' => getCompany()->financialPeriods->firstWhere('status', "During") ?? ' ']))->disabled(fn() => !getCompany()->financialPeriods->firstWhere('status', "During")),

                // Action::make('Advance Journal Report')
                //     ->modal()->form([
                //         Select::make('financial_period_id')->options(getCompany()->financialPeriods()->pluck('name', 'id'))->required()->label('Financial Period')
                //             ->default(FinancialPeriod::query()->where('company_id', getCompany()->id)->firstWhere('status', "During")?->id)->searchable()->preload(),
                //         DateRangePicker::make('date'),
                //     ])
                //     ->action(function (array $data) {
                //         return redirect()->route('pdf.jornal', [
                //             'period' => $data['financial_period_id'],
                //             'date' => str_replace('/','-',$data['date']),
                //         ]);
                //     }),
                    // Action::make('Account Report')
                    // ->modal()->form([
                    //     Select::make('financial_period_id')->options(getCompany()->financialPeriods()->pluck('name', 'id'))->required()->label('Financial Period')
                    //         ->default(FinancialPeriod::query()->where('company_id', getCompany()->id)->firstWhere('status', "During")?->id)->searchable()->preload(),
                    //         Select::make('account_id')->multiple()->required()->options(getCompany()->accounts->pluck('name','id')),
                    //         DateRangePicker::make('date')

                    // ])
                    // ->action(function (array $data) {
                    //     if ($data['date']){
                    //         return redirect()->route('pdf.account', [
                    //             'period' => $data['financial_period_id'],
                    //             'account' => implode('-',$data['account_id']),
                    //             'date'=>str_replace('/','-',$data['date'])
                    //         ]);
                    //     }else{
                    //         return redirect()->route('pdf.account', [
                    //             'period' => $data['financial_period_id'],
                    //             'account' => implode('-',$data['account_id']),
                    //         ]);
                    //     }

                    // }),
                    // Action::make('Trial Balance')
                    // ->action(function (array $data) {
                    //     return redirect()->route('pdf.trialBalance');
                    // }),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex()
                ->tooltip(fn($record) => "CreatedBy:" . $record->user->name." ".$record->created_at),
                Tables\Columns\TextColumn::make('invoice.number')
                ->label('Voc No')
                ->sortable()
                ->searchable(),
                Tables\Columns\TextColumn::make('invoice.date')
                ->label('Date')
                ->state(fn($record)=>Carbon::parse($record->invoice->date)->format("Y-m-d"))
                ->sortable(),
                Tables\Columns\TextColumn::make('Account.name')
                    ->label('Account Name')
                    ->sortable()
                    ->searchable(),
                    Tables\Columns\TextColumn::make('Account.code')
                    ->label('Account Code')
                    ->sortable()
                    ->searchable(),
                    Tables\Columns\TextColumn::make('description')
                    ->sortable(),
                    Tables\Columns\TextColumn::make('debtor')
                        ->label('Debtor')->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total'))
                        ->numeric()
                        ->sortable(),
                Tables\Columns\TextColumn::make('creditor')
                    ->label('Creditor')->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('financialPeriod.name')
                    ->label('Financial Period')
                    ->toggleable()
                    ->toggledHiddenByDefault()

                    ->sortable(),



            ])
            ->filters([
                Tables\Filters\SelectFilter::make('financial_period_id')->label('Financial Period')->searchable()->preload()->options(FinancialPeriod::query()->where('company_id', getCompany()->id)->pluck('name', 'id'))->default(getCompany()->financialPeriods()->where('status', "During")->first()?->id),
                Tables\Filters\SelectFilter::make('invoice_id')->label('Financial Document')->searchable()->preload()->options(Invoice::query()->where('company_id', getCompany()->id)->pluck('name', 'id')),
                Tables\Filters\Filter::make('filter')->form([
                    Select::make('account_id')->relationship('account', 'name', fn(Builder $query) => $query->where('company_id', getCompany()->id))->searchable()->preload()->multiple()
                ])->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['account_id'],
                            fn (Builder $query, $date): Builder => $query->whereIn('account_id',$data['account_id']),
                        );
                })
            ], getModelFilter())
            ->actions([

//                Tables\Actions\ViewAction::make('view')->color('view')->infolist([
//                    Section::make([
//                        ImageEntry::make('img')->columnSpanFull()->alignCenter(),
//                        TextEntry::make('account.holder_name')->color('aColor')->badge()->url(fn($record) => AccountResource::getUrl('edit', ['record' => $record->account_id]))->label('Account'),
//                        TextEntry::make('transactionable.title')->label('Title'),
//                        TextEntry::make('transactionable.data.name')->badge()->color('aColor')->url(fn($record) => $record->transactionable_type === "App\Models\Expense" ? VendorResource::getUrl('edit', ['record' => $record->transactionable_id]) : CustomerResource::getUrl('edit', ['record' => $record->transactionable_id]))->label('Vendor/Customer'),
//                        TextEntry::make('amount_pay')->alignCenter()->tooltip(fn($record) => "Balance Amount : " . $record->balance_amount)->badge()->color(fn($record) => $record->transactionable_type === "App\Models\Expense" ? 'danger' : "success")->label('Amount')->alignCenter(),
//                        // TextEntry::make('payment_date')->dateTime()->label('Payment Date'),
//                        TextEntry::make('reference')->label('Reference'),
//                    ])->columns(3)
//                ]),
            ])
            ->bulkActions([
                Tables\Actions\ExportBulkAction::make()
                    ->exporter(TransactionExporter::class)->color('purple')
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
            'index' => Pages\ListTransActions::route('/'),
            //            'create' => Pages\CreateTransAction::route('/create'),
            //            'edit' => Pages\EditTransAction::route('/{record}/edit'),
        ];
    }
}
