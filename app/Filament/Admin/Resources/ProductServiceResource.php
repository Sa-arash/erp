<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ProductServiceResource\Pages;
use App\Filament\Admin\Resources\ProductServiceResource\RelationManagers;
use App\Filament\Clusters\StackManagementSettings;
use App\Models\Account;
use App\Models\Product;
use App\Models\Transaction;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\Rules\Unique;

class ProductServiceResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $label="Service";
    protected static ?string $navigationGroup = 'Logistic Management';
    protected static ?string $pluralLabel = "Service";
    protected static ?string $cluster = StackManagementSettings::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')->label('Service Name')->required()->maxLength(255),
                Forms\Components\TextInput::make('sku')->label(' Service Code')
                    ->unique(ignoreRecord:true ,modifyRuleUsing: function (Unique $rule) {
                        return $rule->where('company_id', getCompany()->id);
                    })->default(function () {
                        $product = Product::query()->where('product_type','service')->where('company_id',getCompany()->id)->latest()->first();
                        if ($product) {

                            return generateNextCodeProduct($product->sku);
                        }
                    })
                    ->required()->maxLength(255),
                Select::make('account_id')->options(function (Get $get) {
                        $data = [];
                        if (getCompany()->product_service_accounts) {
                            $accounts = Account::query()
                                ->whereIn('id', getCompany()->product_service_accounts)
                                ->where('company_id', getCompany()->id)->get();
                        } else {
                            $accounts = Account::query()
                                ->where('company_id', getCompany()->id)->get();
                        }
                        foreach ($accounts as $account) {
                            $data[$account->id] = $account->name . " (" . $account->code . ")";
                        }
                        return $data;
                    }
                )->required()->model(Transaction::class)->searchable()->label('Category'),
                SelectTree::make('account_id')->formatStateUsing(function ($state, Forms\Set $set) {
                    $account = Account::query()->where('id', $state)->whereNot('currency_id', defaultCurrency()?->id)->first();
                    if ($account) {
                        $set('currency_id', $account->currency_id);
                        $set('exchange_rate', number_format($account->currency->exchange_rate));
                        $set('isCurrency', 1);
                        return $state;
                    }
                    $set('isCurrency', 0);
                    return $state;
                })->defaultOpenLevel(3)->label('SubCategory')->required()->relationship('Account', 'name', 'parent_id', modifyQueryUsing: fn($query) => $query->where('level', '!=', 'control')->where('group','Expanse')->where('company_id', getCompany()->id),modifyChildQueryUsing: fn($query,Get $get)=>$query->where('parent_id',$get('account_id') ? $get('account_id'):"-1"))->searchable(),
                Forms\Components\Textarea::make('description')->columnSpanFull(),
                Forms\Components\Hidden::make('product_type')->default('service')->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->query(Product::query()->where('product_type','service'))
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('title')->label('Service Name')->searchable(),
                Tables\Columns\TextColumn::make('sku')->label('Service Code')->searchable(),
                Tables\Columns\TextColumn::make('account.title')->label('Category ')->sortable(),
                Tables\Columns\TextColumn::make('subAccount.title')->label('Sub Category ')->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListProductServices::route('/'),
            'create' => Pages\CreateProductService::route('/create'),
            'edit' => Pages\EditProductService::route('/{record}/edit'),
        ];
    }
}
