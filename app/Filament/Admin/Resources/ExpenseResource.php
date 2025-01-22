<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ExpenceResource\Pages;
use App\Filament\Admin\Resources\ExpenceResource\RelationManagers;
use App\Filament\Admin\Resources\ExpenseResource\RelationManagers\TransactionsRelationManager;
use App\Models\Account;
use App\Models\Bank_category;
use App\Models\Expense;
use App\Models\ExpensePayment;
use App\Models\Vendor;
use App\Models\VendorType;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconSize;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Component;

use Livewire\Component as Livewire;
use Malzariey\FilamentDaterangepickerFilter\Enums\OpenDirection;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class ExpenseResource extends Resource
{
    protected static ?int $navigationSort = 4;
    protected static ?string $model = Expense::class;
    protected static ?string $label = 'Expenses';

    protected static ?string $navigationGroup = 'Finance';

    protected static ?string $navigationIcon = 'heroicon-m-document-minus';

    public static function canAccess(): bool
    {
        return false;
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
                Forms\Components\DatePicker::make('date')->required(),
                Forms\Components\TextInput::make('amount')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)->required()->mask(RawJs::make('$money($input)'))->stripCharacters(',')->maxLength(255),
                Forms\Components\TextInput::make('reference')->label('Factor Number')->maxLength(255)->nullable()->columnSpanFull(),
                Select::make('vendor_id')->label('Vendor')
                ->createOptionForm([
                 Forms\Components\Section::make([
                     Forms\Components\FileUpload::make('img')->label('Logo\Profile Picture')->image()->columnSpanFull()->imageEditor()->extraAttributes(['style'=>'width:150px!important;border-radius:10px !important']),
                     Forms\Components\TextInput::make('name')->label('Company/Name')->required()->maxLength(255),
                     SelectTree::make('vendor_type_id')->createOptionForm([
                         Forms\Components\Section::make([
                             Forms\Components\TextInput::make('title')->required()->maxLength(255),
                             Forms\Components\Select::make('parent_id')->label('Parent')->searchable()->preload()->options(VendorType::query()->where('type',0)->where('company_id',getCompany()->id)->pluck('title','id')),
                             Forms\Components\Textarea::make('description')->columnSpanFull()->maxLength(255),
                         ])->columns()
                     ])->createOptionUsing(function (array $data): int {
                         $data['company_id'] = getCompany()->id;
                         $data['type'] = 0;
                         return VendorType::query()->create($data)->getKey();
                     })->model(Vendor::class)->label('VendorType')->withCount()->defaultOpenLevel(2)->required()->searchable()->relationship('vendorType','title','parent_id', modifyQueryUsing: fn($query) => $query->where('type',0)->where('company_id',getCompany()->id), modifyChildQueryUsing: fn($query) => $query->where('type',0)->where('company_id',getCompany()->id)),
                     Forms\Components\TextInput::make('NIC')->unique('vendors','NIC',ignoreRecord: true)->label('NIC')->nullable()->maxLength(255),
                     Forms\Components\TextInput::make('phone')->tel()->numeric()->nullable()->maxLength(255),
                     Forms\Components\TextInput::make('website')->nullable()->suffixIcon('heroicon-c-globe-americas')->maxLength(255),
                     Forms\Components\TextInput::make('email')->unique('vendors','email',ignoreRecord: true)->email()->nullable()->maxLength(255),
                     Forms\Components\Select::make('country')->nullable()->options(getCountry())->searchable()->preload(),
                     Forms\Components\TextInput::make('state')->label('State/Province')->nullable()->maxLength(255),
                     Forms\Components\TextInput::make('city')->columnSpanFull()->nullable()->maxLength(255),
                     Forms\Components\Textarea::make('description')->columnSpanFull(),
                 ])->columns()
                ])
                ->createOptionUsing(function (array $data): int {
                    return Vendor::query()->create([
                        'img' => $data['img'],
                        'name' => $data['name'],
                        'vendor_type_id' => $data['vendor_type_id'],
                        'NIC' => $data['NIC'],
                        'phone' => $data['phone'],
                        'website' => $data['website'],
                        'email' => $data['email'],
                        'country' => $data['country'],
                        'state' => $data['state'],
                        'city' => $data['city'],
                        'description' => $data['description'],
                        'company_id' => getCompany()->id
                    ])->getKey();
                })->options(getCompany()->vendors()->pluck('name', 'id'))->searchable()->preload()->required(),
                SelectTree::make('category_id')->createOptionForm([
                    Forms\Components\Section::make([
                        Forms\Components\TextInput::make('title')->required()->maxLength(255),
                        Forms\Components\Select::make('parent_id')->label('Parent')->searchable()->preload()->options(Bank_category::query()->where('type', 0)->where('company_id', getCompany()->id)->pluck('title', 'id')),
                        Forms\Components\Textarea::make('description')->columnSpanFull()->maxLength(255),
                    ])->columns()
                ])->createOptionUsing(function (array $data): int {
                    $data['company_id'] = getCompany()->id;
                    $data['type'] = 0;
                    return Bank_category::query()->create($data)->getKey();
                })->withCount()->defaultOpenLevel(2)->label('Category')->required()->searchable()->relationship('category', 'title', 'parent_id', modifyQueryUsing: fn($query) => $query->where('type', 0)->where('company_id', getCompany()->id), modifyChildQueryUsing: fn($query) => $query->where('type', 0)->where('company_id', getCompany()->id)),
                Forms\Components\Textarea::make('description')->columnSpanFull(),
                Forms\Components\FileUpload::make('payment_receipt_image')->columnSpanFull()->image()->imageEditor(),
                Forms\Components\Repeater::make('transaction')->label('Transaction')->relationship('transactions')
                    ->schema([
                        Forms\Components\Section::make([
                            Select::make('account_id')
                                ->label('From Account')
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
                            'amount'=>$account->amount-$data['amount_pay']
                        ]);
                        $data['balance_amount'] = $account->amount;
                        return $data;
                    })->visible(fn(string $operation)=> $operation==="create" )->defaultItems(0)->addActionLabel('Add Transaction')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('created_at','desc')
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),

                Tables\Columns\TextColumn::make('title')->sortable()->alignCenter(),
                Tables\Columns\TextColumn::make('date')->date()->sortable()->alignCenter(),
                Tables\Columns\TextColumn::make('amount')->numeric()->sortable()->badge()->alignCenter(),
                Tables\Columns\TextColumn::make('transactions')->color('aColor')->url(fn($record)=>TransActionResource::getUrl('index',['tableFilters[expense][value]'=>$record->id]))->sortable(query: function (Builder $query, string $direction): Builder {return $query->withSum('transactions','amount_pay')->orderBy('transactions_sum_amount_pay',$direction);})->badge()->state(fn($record)=>$record->transactions->sum('amount_pay'))->alignCenter()->label('Paid')->numeric(),
                Tables\Columns\TextColumn::make('remain')->sortable(query: function (Builder $query, string $direction): Builder {return $query->withSum('transactions','amount_pay')->orderBy('transactions_sum_amount_pay',$direction);})->badge()->state(fn($record)=>$record->amount -$record->transactions->sum('amount_pay') )->alignCenter()->label('Remain')->numeric(),
                Tables\Columns\TextColumn::make('reference')->label('Factor Number')->searchable(),
                Tables\Columns\TextColumn::make('vendor.name')->alignCenter()->color('aColor')->alignCenter()->url(fn($record)=> VendorResource::getUrl('edit',['record'=>$record->vendor_id])),
                Tables\Columns\TextColumn::make('category.title')->alignCenter()->color('aColor')->alignCenter()->url(fn($record)=> BankCategoryResource::getUrl('index',['tableSearch'=>$record->category->title])),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('vendor_id')->label('Vendor')->options(Vendor::query()->where('company_id',getCompany()->id)->pluck('name','id'))->searchable()->preload(),
                Tables\Filters\SelectFilter::make('category_id')->label('Category')->options(Bank_category::query()->where('type',0)->where('company_id',getCompany()->id)->pluck('title','id'))->searchable()->preload(),
                DateRangeFilter::make('date')->showDropdowns()->opens(OpenDirection::CENTER),
            ], getModelFilter())
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('payment')->icon('cart')->iconSize(IconSize::Large)->color('success')->requiresConfirmation()->modalIcon('cart')->form([
                    Forms\Components\Section::make([
                        Select::make('account_id')
                            ->label('From Account')
                            ->options(function () {
                                $banks = Account::query()->where('company_id', getCompany()->id)->get();
                                $data = [];
                                foreach ($banks as $bank) {
                                    $data[$bank->id] = $bank->holder_name . "(" . $bank->currency . ")";
                                }
                                return $data;
                            })->live()
                            ->searchable()->preload()
                            ->required(),
                        Forms\Components\TextInput::make('amount_pay')->label(function (Forms\Get $get) {
                            $bank = Account::query()->firstWhere('id', $get('account_id'));
                            if ($bank) {
                                return 'Amount Pay (' . $bank->currency . ')';
                            }
                            return 'Amount Pay';
                        })->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)->required(),
                        Forms\Components\DateTimePicker::make('payment_date')->default(now())->required(),
                        Forms\Components\TextInput::make('reference')->maxLength(255)->nullable(),
                        Forms\Components\FileUpload::make('img')->nullable()->image()->columnSpanFull()


                    ])->columns()
                ])->modalWidth(MaxWidth::ScreenLarge)->action(function ($data, $record) {
                    $account = Account::query()->firstWhere('id', $data['account_id']);
                    $account->update([
                        'amount' => $account->amount - $data['amount_pay']
                    ]);
                    $record->transactions()->create([
                        'account_id' => $data['account_id'],
                        'amount_pay' => $data['amount_pay'],
                        'balance_amount' => $account->amount,
                        'img' => $data['img'],
                        'reference' => $data['reference'],
                        'payment_date' => $data['payment_date'],
                        'company_id' => getCompany()->id,
                        'user_id' => auth()->id(),
                    ]);
                    $record->vendor->update([
                        'total_amount' => $record->vendor->total_amount + $data['amount_pay']
                    ]);


                    Notification::make('payed')->title('payed')->success()->send();
                })
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
            TransactionsRelationManager::class

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
