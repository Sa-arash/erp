<?php

namespace App\Filament\Admin\Resources;

use App\Enums\GenderEnum;
use App\Filament\Admin\Resources\CustomerResource\Pages;
use App\Filament\Admin\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use App\Models\FinancialPeriod;
use App\Models\VendorType;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;

class CustomerResource extends Resource
{
    protected static ?int $navigationSort = 2;
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';
    protected static ?string $navigationGroup = 'Finance Management';
    protected static ?string $label = 'customer';
    public static function canAccess(): bool
    {
        return false;
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
                })->withCount()->defaultOpenLevel(2)->label('CustomerType')->required()->searchable()->relationship('customerType', 'title', 'parent_id', modifyQueryUsing: fn($query) => $query->where('type', 1)->where('company_id', getCompany()->id), modifyChildQueryUsing: fn($query) => $query->where('type', 1)->where('company_id', getCompany()->id)),
                Forms\Components\TextInput::make('NIC')->unique('vendors', 'NIC', ignoreRecord: true)->label('License Number/NIC')->nullable()->maxLength(255),
                Forms\Components\TextInput::make('phone')->tel()->numeric()->nullable()->maxLength(255),
                Forms\Components\TextInput::make('website')->nullable()->suffixIcon('heroicon-c-globe-americas')->maxLength(255),
                Forms\Components\TextInput::make('email')->unique('customers', 'email', ignoreRecord: true)->email()->nullable()->maxLength(255),
                Forms\Components\ToggleButtons::make('gender')->options(['male' => 'male', 'female' => 'female', 'other' => 'other'])->required()->inline()->grouped(),
                Forms\Components\Select::make('country')->nullable()->options(getCountry())->searchable()->preload(),
                Forms\Components\TextInput::make('state')->label('State/Province')->nullable()->maxLength(255),
                Forms\Components\TextInput::make('city')->nullable()->maxLength(255),
                Forms\Components\Textarea::make('description')->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('customerType.title')->color('aColor')->url(fn($record)=>  VendorTypeResource::getUrl('index',['tableSearch'=>$record->customerType->title]))->alignCenter(),
                Tables\Columns\TextColumn::make('amount')->state(fn($record) => $record->incomes->sum('amount'))->sortable(query: function (Builder $query, string $direction): Builder {
                    return $query->withSum('incomes', 'amount')->orderBy('incomes_sum_amount', $direction);
                })->badge()->numeric(),
                Tables\Columns\TextColumn::make('total_amount')
//                    ->state(fn($record)=> $record->transactions->sum('amount_pay'))
                    ->label('Balance')->badge()->numeric(),
                Tables\Columns\TextColumn::make('company.title')->searchable(),

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('vendor_type_id')->label('VendorType')->searchable()->preload()->options(VendorType::query()->where('company_id', getCompany()->id)->pluck('title', 'id'))

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                
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
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
