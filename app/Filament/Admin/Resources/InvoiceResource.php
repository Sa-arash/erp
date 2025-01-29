<?php

namespace App\Filament\Admin\Resources;

use App\Enums\ChequeStatus;
use App\Filament\Admin\Resources\InvoiceResource\Pages;
use App\Filament\Admin\Resources\InvoiceResource\RelationManagers;
use App\Models\FinancialPeriod;
use App\Models\Invoice;
use Closure;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;
    protected static ?string $label = 'Voucher';
    protected static ?string $pluralLabel = 'Journal Entry';
    protected static ?string $navigationIcon = 'heroicon-o-document';
    protected static ?string $navigationLabel =  'Journal Entry';
    protected static ?int $navigationSort = 0;
    protected static ?string $navigationGroup = 'Finance Management';

    public static function canEdit(Model $record): bool
    {
        if (isset($record->transactions[0])) {
            return ($record->transactions[0]->financialPeriod->end_date > now());
        }
        return false;
    }

    public static function canAccess(): bool
    {
        $period = FinancialPeriod::query()->where('company_id', getCompanyUrl())->where('status', 'During')->first();
        if ($period) {
            return true;
        }
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
               ])->columns(8),

                Forms\Components\Section::make([
                    Forms\Components\Repeater::make('transactions')->label('')->relationship('transactions')->schema([
                        SelectTree::make('account_id')->defaultOpenLevel(3)->live()->label('Account')->required()->relationship('Account', 'name', 'parent_id', modifyQueryUsing: fn($query) => $query->where('level', '!=', 'control')->where('company_id', getCompany()->id))->searchable(),
                        Forms\Components\TextInput::make('description')->required(),

                        Forms\Components\TextInput::make('debtor')->afterStateUpdated(function ($state,Forms\Set $set){$set('cheque.amount',$state);})
                        ->mask(RawJs::make('$money($input)'))->stripCharacters(',')
                        ->suffixIcon('cash')->suffixIconColor('success')->required()->default(0)->minValue(0)
                        ->rules([
                            fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                                if ( $get('debtor') == 0 && $get('creditor') == 0) {
                                    $fail('Only one of these values can be zero.');
                                }elseif($get('debtor') != 0 && $get('creditor') != 0){
                                    $fail('At least one of the values must be zero.');
                                }
                            },
                        ]),

                        Forms\Components\TextInput::make('creditor')
                        ->afterStateUpdated(function ($state,Forms\Set $set){$set('cheque.amount',$state);})
                        ->mask(RawJs::make('$money($input)'))->stripCharacters(',')
                        ->mask(RawJs::make('$money($input)'))->stripCharacters(',')
                        ->suffixIcon('cash')->suffixIconColor('success')->required()->default(0)->minValue(0)
                        ->rules([
                            fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                                if ( $get('debtor') == 0 && $get('creditor') == 0) {
                                    $fail('Only one of these values can be zero.');
                                }elseif($get('debtor') != 0 && $get('creditor') != 0){
                                    $fail('At least one of the values must be zero.');
                                }
                            },
                        ]),
                        Forms\Components\Checkbox::make('Cheque')->inline()->live(),
                      Forms\Components\Section::make([
                          Forms\Components\Fieldset::make('cheque')->relationship('cheque')->schema([
                              Forms\Components\TextInput::make('cheque_number')->required()->maxLength(255),
                              Forms\Components\TextInput::make('amount')->default(function (Get $get){

                                  if ($get('debtor')>0){
                                      return $get('debtor');

                                  }
                                  if ($get('creditor') >0){
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
                              Forms\Components\ToggleButtons::make('type')->options([0=>'Receivable',1=>'Payable'])->inline()->grouped()->required(),
                              Forms\Components\Hidden::make('company_id')->default(getCompany()->id)
                          ]),
                      ])->collapsible()->persistCollapsed()->visible(fn(Forms\Get $get)=>$get('Cheque')),
                        Forms\Components\Hidden::make('financial_period_id')->required()->label('Financial Period')
                            ->default(FinancialPeriod::query()->where('company_id', getCompany()->id)->firstWhere('status', "During")?->id)
                    ])->minItems(2)->columns(5)->defaultItems(2)
                        ->mutateRelationshipDataBeforecreateUsing(function (array $data): array {
                            $data['user_id'] = auth()->id();
                            $data['company_id'] = getCompany()->id;
                            $data['period_id'] = FinancialPeriod::query()->where('company_id', getCompany()->id)->where('status', "During")->first()->id;
                            return $data;
                        })
                ])->columns(1)->columnSpanFull()

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('number')->searchable()->label('Voucher NO'),
                Tables\Columns\TextColumn::make('date')->state(fn($record)=>Carbon::parse($record->date)->format("Y-m-d")),
                Tables\Columns\TextColumn::make('name')->searchable()->label('Voucher Title'),
                Tables\Columns\TextColumn::make('reference')->searchable(),
            ])
            ->filters([
                Tables\Filters\QueryBuilder::make()
                    ->constraints([
                        Tables\Filters\QueryBuilder\Constraints\TextConstraint::make('name')->label('Voucher Title'),
                        Tables\Filters\QueryBuilder\Constraints\TextConstraint::make('number')->label('Voucher NO'),
                        Tables\Filters\QueryBuilder\Constraints\TextConstraint::make('reference')->label('Reference'),
                        Tables\Filters\QueryBuilder\Constraints\TextConstraint::make('description')->label('Description'),
                        Tables\Filters\QueryBuilder\Constraints\TextConstraint::make('record_description')->relationship(name: 'transactions', titleAttribute: 'description'),
                        Tables\Filters\QueryBuilder\Constraints\DateConstraint::make('date'),

                    ]),

            ],getModelFilter())
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('print')
                ->label('')
                ->icon('heroicon-o-printer')
                ->url(fn($record)=>route('pdf.document',['document'=>$record->id])),
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
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
