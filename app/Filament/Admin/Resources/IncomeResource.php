<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\IncomeResource\Pages;
use App\Filament\Admin\Resources\IncomeResource\RelationManagers;
use App\Models\Account;
use App\Models\Bank_category;
use App\Models\Customer;
use App\Models\ExpensePayment;
use App\Models\Income;
use App\Models\IncomePayment;
use App\Models\VendorType;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\IconSize;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Livewire\Component as Livewire;
use Malzariey\FilamentDaterangepickerFilter\Enums\OpenDirection;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class IncomeResource extends Resource
{
    protected static ?int $navigationSort = 5;
    protected static ?string $model = Income::class;
    protected static ?string $navigationGroup = 'Finance Management';

    protected static ?string $navigationIcon = 'heroicon-m-document-plus';
    public static function canAccess(): bool
    {
        return false;
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
                Forms\Components\DatePicker::make('date')->default(now())->required(),
                Forms\Components\TextInput::make('amount')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)->required()->mask(RawJs::make('$money($input)'))->stripCharacters(',')->maxLength(255),
                Forms\Components\TextInput::make('reference')->label('Factor Number')->columnSpanFull()->maxLength(255)->nullable(),
                Forms\Components\Hidden::make('company_id')->default(Filament::getTenant()->id)->required(),
                Select::make('customer_id')->label('Customer')
                ->createOptionForm([
                  Forms\Components\Section::make([
                      Forms\Components\FileUpload::make('img')->label('Logo/Profile Picture')->image()->columnSpanFull()->imageEditor()->extraAttributes(['style' => 'width:150px!important;border-radius:10px !important']),
                      Forms\Components\TextInput::make('name')->label('Company/Name')->required()->maxLength(255),
                      SelectTree::make('vendor_type_id')->createOptionForm([
                          Forms\Components\Section::make([
                              Forms\Components\TextInput::make('title')->required()->maxLength(255),
                              Forms\Components\Select::make('parent_id')->label('Parent')->searchable()->preload()->options(VendorType::query()->where('type', 1)->where('company_id', getCompany()->id)->pluck('title', 'id')),
                              Forms\Components\Textarea::make('description')->columnSpanFull()->maxLength(255),
                          ])->columns()
                      ])->createOptionUsing(function (array $data): int {
                          $data['company_id'] = getCompany()->id;
                          $data['type'] = 1;
                          return VendorType::query()->create($data)->getKey();
                      })->model(Customer::class)->withCount()->defaultOpenLevel(2)->label('CustomerType')->required()->searchable()->relationship('customerType', 'title', 'parent_id', modifyQueryUsing: fn($query) => $query->where('type', 1)->where('company_id', getCompany()->id), modifyChildQueryUsing: fn($query) => $query->where('type', 1)->where('company_id', getCompany()->id)),
                      Forms\Components\TextInput::make('NIC')->unique('vendors', 'NIC', ignoreRecord: true)->label('License Number/NIC')->nullable()->maxLength(255),
                      Forms\Components\TextInput::make('phone')->tel()->numeric()->nullable()->maxLength(255),
                      Forms\Components\TextInput::make('website')->nullable()->suffixIcon('heroicon-c-globe-americas')->maxLength(255),
                      Forms\Components\TextInput::make('email')->unique('customers', 'email', ignoreRecord: true)->email()->nullable()->maxLength(255),
                      Forms\Components\ToggleButtons::make('gender')->options(['male' => 'male', 'female' => 'female', 'other' => 'other'])->required()->inline()->grouped(),
                      Forms\Components\Select::make('country')->nullable()->options(getCountry())->searchable()->preload(),
                      Forms\Components\TextInput::make('state')->label('State/Province')->nullable()->maxLength(255),
                      Forms\Components\TextInput::make('city')->nullable()->maxLength(255),
                      Forms\Components\Textarea::make('description')->columnSpanFull(),
                  ])->columns()

                ])
                ->createOptionUsing(function (array $data): int {
                    return Customer::query()->create([
                        'img' => $data['img'],
                        'name' => $data['name'],
                        'NIC' => $data['NIC'],
                        'phone' => $data['phone'],
                        'website' => $data['website'],
                        'email' => $data['email'],
                        'vendor_type_id' => $data['vendor_type_id'],
                        'gender' => $data['gender'],
                        'country' => $data['country'],
                        'state' => $data['state'],
                        'city' => $data['city'],
                        'description' => $data['description'],
                        'company_id' => getCompany()->id
                    ])->getKey();
                })
                ->options(getCompany()->customers()->pluck('name', 'id'))->searchable()->preload()->required(),
                SelectTree::make('category_id')->createOptionForm([
                    Forms\Components\Section::make([
                        Forms\Components\TextInput::make('title')->required()->maxLength(255),
                        Forms\Components\Select::make('parent_id')->label('Parent')->searchable()->preload()->options(Bank_category::query()->where('type', 1)->where('company_id', getCompany()->id)->pluck('title', 'id')),
                        Forms\Components\Textarea::make('description')->columnSpanFull()->maxLength(255),
                    ])->columns()
                ])->createOptionUsing(function (array $data): int {
                    $data['company_id'] = getCompany()->id;
                    $data['type'] = 1;
                    return Bank_category::query()->create($data)->getKey();
                })->withCount()->defaultOpenLevel(2)->label('Category')->required()->searchable()->relationship('category', 'title', 'parent_id', modifyQueryUsing: fn($query) => $query->where('type', 1)->where('company_id', getCompany()->id), modifyChildQueryUsing: fn($query) => $query->where('type', 1)->where('company_id', getCompany()->id)),
                Forms\Components\Textarea::make('description')->columnSpanFull(),
                Forms\Components\FileUpload::make('payment_receipt_image')->image()->imageEditor()->columnSpanFull(),
                Forms\Components\Repeater::make('transaction')->label('Transaction')->relationship('transactions')
                    ->schema([
                        Forms\Components\Section::make([
                            Select::make('account_id')
                                ->label('To Account')
                                ->options(function () {
                                    $banks = Account::query()->where('company_id', getCompany()->id)->get();
                                    $data = [];
                                    foreach ($banks as $bank) {
                                        $data[$bank->id] = $bank->holder_name . "(" . $bank->currency. ")";
                                    }
                                    return $data;
                                })->live()
                                ->searchable()->preload()
                                ->required(),
                            Forms\Components\TextInput::make('amount_pay')->label(function(Forms\Get $get){
                                $bank=  Account::query()->firstWhere('id',$get('account_id'));
                                if ($bank){
                                    return 'Amount Pay ('. $bank->currency.')';
                                }
                                return 'Amount Pay';
                            })->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)->required(),
                            Forms\Components\DateTimePicker::make('payment_date')->default(now())->required(),
                            Forms\Components\TextInput::make('reference')->maxLength(255)->nullable(),
                            Forms\Components\FileUpload::make('img')->nullable()->image()->columnSpanFull()


                        ])->columns()
                    ])
                  ->columnSpanFull()->maxItems(1) ->reorderable(false)
                    ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                        $data['user_id'] = auth()->id();
                        $data['company_id'] = getCompany()->id;

                        $account=Account::query()->firstWhere('id',$data['account_id']);
                        $account->update([
                            'amount'=>$account->amount+$data['amount_pay']
                        ]);
                        $data['balance_amount'] = $account->amount;

                        return $data;
                    })->visible(fn(string $operation)=> $operation==="create" )->defaultItems(0)->addActionLabel('Add Transaction')



            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex()->alignCenter(),
                Tables\Columns\TextColumn::make('customer.name')->color('aColor')->url(fn($record)=> CustomerResource::getUrl('edit',['record'=>$record->customer_id]))->numeric()->sortable(),
                Tables\Columns\TextColumn::make('title')->sortable()->searchable()->alignCenter(),
                Tables\Columns\TextColumn::make('date')->searchable()->date()->sortable()->alignCenter(),
                Tables\Columns\TextColumn::make('amount')->numeric()->sortable()->badge()->alignCenter(),
                Tables\Columns\TextColumn::make('transactions')->color('aColor')->url(fn($record)=>TransActionResource::getUrl('index',['tableFilters[income][value]'=>$record->id]))->state(fn($record)=>$record->transactions->sum('amount_pay'))->sortable(query: function (Builder $query, string $direction): Builder {return $query->withSum('transactions','amount_pay')->orderBy('transactions_sum_amount_pay',$direction);})->alignCenter()->badge()->label('Payed')->numeric(),
                Tables\Columns\TextColumn::make('due')->state(fn($record)=> $record->amount - $record->transactions->sum('amount_pay'))->numeric()->badge()->alignCenter(),
                Tables\Columns\TextColumn::make('category.title')->color('aColor')->url(fn($record)=> BankCategoryResource::getUrl('index',['tableSearch'=>$record->category->title]))->numeric()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('customer_id')->label('Customer')->relationship('customer','name')->searchable()->preload(),
                Tables\Filters\SelectFilter::make('category_id')->label('Category')->relationship('category','title')->searchable()->preload(),
                DateRangeFilter::make('date')->showDropdowns()->opens(OpenDirection::CENTER),
            ],getModelFilter())
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('payment')->icon('cart')->iconSize(IconSize::Large)->color('success')->requiresConfirmation()->modalIcon('cart')->form([
                    Forms\Components\Section::make([
                        Select::make('account_id')
                            ->label('To Account')
                            ->options(function () {
                                $banks = Account::query()->where('company_id', getCompany()->id)->get();
                                $data = [];
                                foreach ($banks as $bank) {
                                    $data[$bank->id] = $bank->holder_name . "(" . $bank->currency. ")";
                                }
                                return $data;
                            })->live()
                            ->searchable()->preload()
                            ->required(),
                        Forms\Components\TextInput::make('amount_pay')->label(function(Forms\Get $get){
                            $bank=  Account::query()->firstWhere('id',$get('account_id'));
                            if ($bank){
                                return 'Amount Pay ('. $bank->currency.')';
                            }
                            return 'Amount Pay';
                        })->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)->required(),
                        Forms\Components\DateTimePicker::make('payment_date')->default(now())->required(),
                        Forms\Components\TextInput::make('reference')->maxLength(255)->nullable(),
                        Forms\Components\FileUpload::make('img')->nullable()->image()->columnSpanFull()


                    ])->columns()
                ])->modalWidth(MaxWidth::ScreenLarge)->action(function ($data,$record){
                    $account=Account::query()->firstWhere('id',$data['account_id']);
                    $account->update([
                        'amount'=>$account->amount+$data['amount_pay']
                    ]);
                    $record->transactions()->create([
                        'account_id'=>$data['account_id'],
                        'amount_pay'=>$data['amount_pay'],
                        'balance_amount'=>$account->amount,
                        'img'=>$data['img'],
                        'reference'=>$data['reference'],
                        'payment_date'=>$data['payment_date'],
                        'company_id'=>getCompany()->id,
                        'user_id'=>auth()->id(),
                    ]);
                    $record->customer->update([
                        'total_amount'=>$record->customer->total_amount +$data['amount_pay']
                    ]);
                    Notification::make('payed')->title('payed')->success()->send();
                })->modalSubmitActionLabel('Payment')
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
            RelationManagers\TransactionsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIncomes::route('/'),
            'create' => Pages\CreateIncome::route('/create'),
            'edit' => Pages\EditIncome::route('/{record}/edit'),
        ];
    }
}
