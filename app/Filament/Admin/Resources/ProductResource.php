<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ProductResource\Pages;
use App\Filament\Admin\Resources\ProductResource\RelationManagers;
use App\Filament\Admin\Widgets\Accounting;
use App\Filament\Clusters\StackManagementSettings;
use App\Models\Account;
use App\Models\Department;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\Unit;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists\Components\ImageEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;
use TomatoPHP\FilamentMediaManager\Form\MediaManagerInput;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationGroup = 'Logistic Management';
    protected static ?string $pluralLabel = "Product";
    protected static ?string $label="Product";

    protected static ?string $navigationIcon = 'heroicon-m-cube';
    protected static ?string $cluster = StackManagementSettings::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make([
                    Select::make('department_id')->live()->label('Department')->required()->options(getCompany()->departments->pluck('title','id'))->searchable()->preload()->afterStateUpdated(function (Get $get,Set $set,$state){
                        $department=Department::query()->firstWhere('id',$state);
                        if ($department){
                            $product = Product::query()->where('department_id',$state)->where('company_id',getCompany()->id)->latest('sku')->first();
                            if ($product) {
                                $set('sku',generateNextCodeProduct($product->sku));
                            }else{
                                $set('sku',$department->abbreviation."-0001");
                            }
                        }
                    }),
                    Select::make('product_type')->searchable()->options(['consumable' => 'Consumable', 'unConsumable' => 'non-Consumable'])->default('consumable')->live()->afterStateUpdated(function(Set $set){
                        $set('account_id',null);
                    }),
                    Forms\Components\TextInput::make('sku')->readOnly()->label(' SKU')->unique(ignoreRecord:true ,modifyRuleUsing: function (Unique $rule) {
                            return $rule->where('company_id', getCompany()->id);
                        })->required()->maxLength(255),
                    Forms\Components\TextInput::make('title')->label('Material Specification')->required()->maxLength(255),
                    Forms\Components\TextInput::make('second_title')->label('Specification in Dari Language')->nullable()->maxLength(255),
                    Select::make('unit_id')->required()->relationship('unit','title',fn($query)=>$query->where('company_id',getCompany()->id))->searchable()->preload()->createOptionForm([
                        Forms\Components\TextInput::make('title')->label('Unit Name')->unique('units', 'title')->required()->maxLength(255),
                        Forms\Components\Toggle::make('is_package')->live()->required(),
                        Forms\Components\TextInput::make('items_per_package')->numeric()->visible(fn(Get $get) => $get('is_package'))->default(null),
                    ])->createOptionUsing(function ($data) {
                        $data['company_id'] = getCompany()->id;
                        Notification::make('success')->success()->title('Create Unit')->send();
                        return  Unit::query()->create($data)->getKey();
                    }),
                ])->columns(3),

                Select::make('account_id')->options(function (Get $get) {

                   if($get('product_type')=='unConsumable')
                   {

                    $data=[];
                    if (getCompany()->product_accounts){
                        $accounts= Account::query()
                            ->whereIn('id', getCompany()->product_accounts)
                            ->where('company_id', getCompany()->id)->get();
                    }else{
                        $accounts= Account::query()
                            ->where('company_id', getCompany()->id)->get();
                    }
                    foreach ( $accounts as $account){
                        $data[$account->id]=$account->name." (".$account->code .")";
                    }
                    return $data;
                }elseif($get('product_type')=='consumable'){
                    $data=[];
                    if (getCompany()->product_expence_accounts){
                        $accounts= Account::query()
                            ->whereIn('id', getCompany()->product_expence_accounts)
                            ->where('company_id', getCompany()->id)->get();
                    }else{
                        $accounts= Account::query()
                            ->where('company_id', getCompany()->id)->get();
                    }
                    foreach ( $accounts as $account){
                        $data[$account->id]=$account->name." (".$account->code .")";
                    }
                    return $data;
                }
                })->required()->model(Transaction::class)->searchable()->label('Category')->live(),

                Select::make('sub_account_id')->label('SubCategory')->required()->options(function (Get $get){
                    $parent=$get('account_id');
                 if ($parent){
                     $accounts =  Account::query()
                         ->where('parent_id', $parent)
                         ->orWhereHas('account', function ($query) use ($parent) {
                             return $query->where('parent_id', $parent)->orWhereHas('account', function ($query) use ($parent) {
                                 return $query->where('parent_id', $parent);
                             });
                         })
                         ->get();
                     $data=[];
                     foreach ($accounts as $account){
                         $data[$account->id]=$account->title;
                     }
                     return $data;
                 }
                   return  [];
                })->searchable(),
                Forms\Components\Textarea::make('description')->columnSpanFull(),
               Section::make([
                   MediaManagerInput::make('photo')->columnSpan(1)->label('Upload Image')->image(true)->orderable(false)->disk('public')->schema([])->maxItems(1),
                   Forms\Components\TextInput::make('stock_alert_threshold')->numeric()->default(5)->required(),
               ])->columns(4)

                // Forms\Components\TextInput::make('price')
                // ->mask(RawJs::make('$money($input)'))->stripCharacters(',')
                //     ->numeric()
                //     ->prefix('$'),
                // Forms\Components\DatePicker::make('expiration_date')->default(now()),
            ]);


    }

    public static function table(Table $table): Table
    {
        return $table->query(Product::query()->whereIn('product_type',['unConsumable','consumable']))
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('sku')->label('SKU')->searchable(),
                Tables\Columns\ImageColumn::make('image')->defaultImageUrl(asset('img/images.jpeg'))->state(function ($record){
                    return $record->media->first()?->original_url;
                })->action(Tables\Actions\Action::make('image')->modalSubmitAction(false)->infolist(function ($record){
                    if ($record->media->first()?->original_url){
                        return  [
                            \Filament\Infolists\Components\Section::make([
                                ImageEntry::make('image')->label('')->width(650)->height(650)->columnSpanFull()->state($record->media->first()?->original_url)
                            ])
                        ];
                    }
                })),
                Tables\Columns\TextColumn::make('title')->label('Product Name')->searchable(),
                Tables\Columns\TextColumn::make('account.title')->label('Category ')->sortable(),
                Tables\Columns\TextColumn::make('subAccount.title')->label('Sub Category ')->sortable(),
                Tables\Columns\TextColumn::make('product_type')->state(function($record){
                    if ($record->product_type==='consumable'){
                        return 'Consumable';
                    }elseif($record->product_type==='unConsumable'){
                        return 'Non-Consumable';
                    }
                }),
                Tables\Columns\TextColumn::make('count')->numeric()->state(fn($record) => $record->assets->count())->label('Quantity')->badge()
                ->color(fn($record)=>$record->assets->count()>$record->stock_alert_threshold ? 'success' : 'danger')->tooltip(fn($record)=>'Stock Alert:'.$record->stock_alert_threshold),
                Tables\Columns\TextColumn::make('price')->numeric()->state(fn($record) => $record->assets->sum('price'))->label('Total Value')->badge()->color('success')

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('department_id')->label('Department')->options(getCompany()->departments->pluck('title','id'))->searchable()->preload()

//                SelectFilter::make('unit_id')->searchable()->preload()->options(Unit::where('company_id', getCompany()->id)->get()->pluck('title', 'id'))
//                    ->label('Unit'),

            ], getModelFilter())
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
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
            RelationManagers\AssetsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
            'view' => Pages\ViewProduct::route('/{record}/view'),
        ];
    }
}
