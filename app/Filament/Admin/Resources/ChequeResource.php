<?php

namespace App\Filament\Admin\Resources;

use App\Enums\ChequeStatus;
use App\Filament\Admin\Resources\ChequeResource\Pages;
use App\Filament\Admin\Resources\ChequeResource\RelationManagers;
use App\Filament\Admin\Resources\ChequeResource\Widgets\StateCheque;
use App\Models\Cheque;
use App\Models\Invoice;
use App\Models\Transaction;
use Carbon\Carbon;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ChequeResource extends Resource
{
    protected static ?string $model = Cheque::class;

    protected static ?string $navigationIcon = 'heroicon-s-pencil-square';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationGroup = 'Finance Management';

    protected static ?string $navigationLabel = 'AR/AP';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make([
                    Forms\Components\TextInput::make('cheque_number')->required()->maxLength(255),
                    Forms\Components\TextInput::make('bank_name')->maxLength(255),
                    Forms\Components\TextInput::make('branch_name')->maxLength(255),
                ])->columns(3),
                Forms\Components\Section::make([
                    Forms\Components\TextInput::make('account_number')->maxLength(255),
                    Forms\Components\DateTimePicker::make('issue_date')->withoutSeconds()->withoutTime()->required(),
                    Forms\Components\DateTimePicker::make('due_date')->withoutSeconds()->withoutTime()->required(),
                ])->columns(3),
                Forms\Components\Section::make([
                    Forms\Components\TextInput::make('amount')->prefix(defaultCurrency()?->symbol)->mask(RawJs::make('$money($input)'))->stripCharacters(',')->required()->numeric(),
                    Forms\Components\TextInput::make('payer_name')->required()->maxLength(255),
                    Forms\Components\TextInput::make('payee_name')->required()->maxLength(255),
                ])->columns(3),
                Forms\Components\Textarea::make('description')->columnSpanFull(),
                Forms\Components\ToggleButtons::make('type')->options([0 => 'Receivable', 1 => 'Payable'])->inline()->grouped()->required()

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('id','desc')
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('transaction.description')->sortable(),
                Tables\Columns\TextColumn::make('amount')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('issue_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('due_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('payer_name')->searchable(),
                Tables\Columns\TextColumn::make('payee_name')->searchable(),
                Tables\Columns\TextColumn::make('Due Days')->label('Due Days')->state(function ($record) {
                        $daysUntilDue = Carbon::make($record->due_date)->diffInDays(now(), false);
                        return abs($daysUntilDue) . ($daysUntilDue < 0 ? ' Days Due' : ' Days Passed');
                    })  
                    ->color(function ($record) {
                        $daysUntilDue = Carbon::make($record->due_date)->diffInDays(now(), false);
                        
                        return match(true) {
                            $daysUntilDue < 0 => 'success',
                            $daysUntilDue === 0 => 'warning',
                            default => 'danger'
                        };
                    })
                    ->badge()
                    ->searchable(),         
                Tables\Columns\TextColumn::make('type')->state(fn($record) => $record->type ? "Payable" : "Receivable")->badge(),
                Tables\Columns\TextColumn::make('status')->badge(),
            ])
            ->filters([
                SelectFilter::make('type')->options([0 => 'Receivable', 1 => 'Payable'])->searchable(),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\TextInput::make('days')->label('Days')->numeric()
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                                $data['days'],
                                fn (Builder $query, $day): Builder => $query->whereDate('due_date', '<=',now()->addDays((int)$day)->endOfDay()),
                            );
                    })
            ], getModelFilter())
            ->actions([
                Tables\Actions\Action::make('invoice')->visible(fn($record)=> (bool)$record->transaction_id)->url(fn($record)=>$record->transaction_id ? InvoiceResource::getUrl('edit',['record'=>$record->transaction->invoice_id]) : false),
                Tables\Actions\EditAction::make()->hidden(fn($record) => $record->status->name === "Paid"),
                Tables\Actions\Action::make('action')->requiresConfirmation()->extraModalFooterActions([
                    Tables\Actions\Action::make('Paid')->label('Paid')->form(function ($record){
                        if ($record->transaction){
                          return [
                              Forms\Components\Section::make([
                                  Forms\Components\TextInput::make('number')->label('Voucher NO')->default(getDocumentCode())->required()->readOnly(),
                                  Forms\Components\TextInput::make('name')->label('Voucher Title')->default(fn($record) => "Cheque " . $record->payer_name . " - " . $record->payee_name . " Paid")->required(),
                                  Forms\Components\DatePicker::make('date')->label('Date ')->required()->default(fn($record) => $record->due_date),
                                  SelectTree::make('account_id')->defaultOpenLevel(3)->required()->live()->label('Account')->model(Transaction::class)->required()->relationship('Account', 'name', 'parent_id', modifyQueryUsing: fn($query) => $query->where('stamp', 'Assets')->where('company_id', getCompany()->id))->searchable(),

                              ])->columns()
                          ];
                        }
                    })->action(function ($data, $record) {
                        if (isset($record->transaction)) {

                            $invoice = Invoice::query()->create([
                                'name' => $data['name'],
                                'number' => $data['number'],
                                'date' => $data['date'],
                                'company_id' => getCompany()->id,
                            ]);

                            if ($record->transaction->debtor > 0) {
                                $invoice->transactions()->create([
                                    'account_id' => $data['account_id'],
                                    'creditor' => 0,
                                    'debtor' => $record->transaction->debtor,
                                    'description' => $data['name'],
                                    'company_id' => getCompany()->id,
                                    'user_id' => auth()->id(),
                                    'financial_period_id' => getPeriod()->id,
                                    'currency_id' => defaultCurrency()?->id
                                ]);
                                $invoice->transactions()->create([
                                    'account_id' => $record->transaction->account?->id,
                                    'creditor' => $record->transaction->debtor,
                                    'debtor' => 0,
                                    'description' => $data['name'],
                                    'company_id' => getCompany()->id,
                                    'user_id' => auth()->id(),
                                    'financial_period_id' => getPeriod()->id,
                                    'currency_id' => defaultCurrency()?->id
                                ]);
                            } else {
                                $invoice->transactions()->create([
                                    'account_id' => $data['account_id'],
                                    'creditor' => $record->transaction->creditor,
                                    'debtor' => 0,
                                    'description' => $data['name'],
                                    'company_id' => getCompany()->id,
                                    'user_id' => auth()->id(),
                                    'financial_period_id' => getPeriod()->id,
                                    'currency_id' => defaultCurrency()?->id
                                ]);
                                $invoice->transactions()->create([
                                    'account_id' => $record->transaction->account?->id,
                                    'creditor' => 0,
                                    'debtor' => $record->transaction->creditor,
                                    'description' => $data['name'],
                                    'company_id' => getCompany()->id,
                                    'user_id' => auth()->id(),
                                    'financial_period_id' => getPeriod()->id,
                                    'currency_id' => defaultCurrency()?->id
                                ]);
                            }
                        }
                        $record->update(['status' => 'paid']);
                        Notification::make('paid-cheque')->success()->title('Check Paid')->send()->sendToDatabase(auth()->user());
                    })->color('success'),
                    Tables\Actions\Action::make('returned')->label('Returned')->requiresConfirmation()->action(function ($record) {
                        $record->update(['status' => "returned"]);
                        Notification::make('Blocked')->success()->title('Returned')->send()->sendToDatabase(auth()->user());
                    })->color('warning'),
//                    Tables\Actions\Action::make('Post_dated')->label('Post Dated')->requiresConfirmation()->action(function ($record){
//                        $record->update(['status'=>"Post_dated"]);
//                        Notification::make('Post_dated-cheque')->success()->title('Post Dated')->send()->sendToDatabase(auth()->user());
//                    })->color('info'),
//                    Tables\Actions\Action::make('Canceled')->label('Cancelled')->requiresConfirmation()->action(function ($record){
//                        $record->update(['status'=>"Cancelled"]);
//                        Notification::make('cancelled-cheque')->success()->title('Cancelled Cheque')->send()->sendToDatabase(auth()->user());
//                    })->color('danger'),
                ])->modalWidth(MaxWidth::ThreeExtraLarge)->modalSubmitAction(false)->hidden(fn($record) => $record->status->name === "Paid")
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

    public static function getWidgets(): array
    {
        return [
            StateCheque::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCheques::route('/'),
            'create' => Pages\CreateCheque::route('/create'),
            'edit' => Pages\EditCheque::route('/{record}/edit'),
        ];
    }
}
