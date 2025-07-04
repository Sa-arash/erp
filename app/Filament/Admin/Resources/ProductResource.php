<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ProductResource\Pages;
use App\Filament\Admin\Resources\ProductResource\RelationManagers;
use App\Filament\Clusters\StackManagementSettings;
use App\Models\Account;
use App\Models\Department;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\Unit;
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
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Unique;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use TomatoPHP\FilamentMediaManager\Form\MediaManagerInput;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationGroup = 'Logistic Management';
    protected static ?string $pluralLabel = "Products";
    protected static ?string $label="Products";

    protected static ?string $navigationIcon = 'heroicon-m-cube';
    protected static ?string $cluster = StackManagementSettings::class;

//    protected static ?string $recordTitleAttribute = 'title';
//    public static function getGlobalSearchResultTitle(Model $record): string | Htmlable
//    {
//        dd($record);
//        return $record->title;
//    }
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
                    Select::make('product_type')->searchable()->options(['consumable' => 'Consumable', 'unConsumable' => 'non-Consumable'])->default('consumable')->live()->afterStateUpdated(function(Set $set,$state){
                        if ($state=="consumable"){
                            $set('account_id',getCompany()->product_expence_accounts[0]);
                        }else{
                            $set('account_id',getCompany()->product_accounts[0]);
                        }
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
                })->required()->model(Transaction::class)->searchable()->label('Category')->live()->default(getCompany()->product_expence_accounts[0]),

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
        return $table
        ->headerActions([
            ExportAction::make()
            ->after(function (){
                if (Auth::check()) {
                    activity()
                        ->causedBy(Auth::user())
                        ->withProperties([
                            'action' => 'export',
                        ])
                        ->log('Export' . "Product");
                }
            })->exports([
                ExcelExport::make()->askForFilename("Product")->withColumns([
                    Column::make('sku')->heading('SKU'),
                    Column::make('title')->heading('Product Name'),
                    Column::make('account.title')->heading('Category '),
                    Column::make('subAccount.title')->heading('Sub Category '),
                    Column::make('product_type')->formatStateUsing(function($record){
                        if ($record->product_type==='consumable'){
                            return 'Consumable';
                        }elseif($record->product_type==='unConsumable'){
                            return 'Non-Consumable';
                        }
                    }),
                    Column::make('id')->formatStateUsing(fn($record) => $record->assets->count())->heading('Quantity'),
                    Column::make('created_at')->formatStateUsing(fn($record) => $record->inventories()?->sum('quantity'))->heading('Available'),

                    Column::make('updated_at')->formatStateUsing(fn($record) => $record->assets->sum('price'))->heading('Total Value')
                ]),
                ])->label('Export Product')->color('purple')
        ])->paginated([5,10,50,100,200])
            ->query(Product::query()->with(['media', 'account', 'subAccount', 'inventories', 'assets'])->withCount('assets')
                ->withSum('inventories as inventories_quantity', 'quantity')
                ->withSum('assets as total_price', 'price')->whereIn('product_type', ['unConsumable', 'consumable']))
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('sku')->label('SKU')->searchable(),
                Tables\Columns\ImageColumn::make('image')->defaultImageUrl(asset('img/images.jpeg'))->state(function ($record){
                    return $record->media->first()?->original_url;
                })
    //                    ->action(Tables\Actions\Action::make('image')->modalSubmitAction(false)->infolist(function ($record){
    //                    if ($record->media->first()?->original_url){
    //                        return  [
    //                            \Filament\Infolists\Components\Section::make([
    //                                ImageEntry::make('image') ->extraAttributes(['loading' => 'lazy'])->label('')->width(650)->height(650)->columnSpanFull()->state($record->media->first()?->original_url)
    //                            ])
    //                        ];
    //                    }
    //                }))
                    ->extraAttributes(['loading' => 'lazy']),
                Tables\Columns\TextColumn::make('title')->label('Product Name')->searchable(),
                Tables\Columns\TextColumn::make('account.title')->label('Category ')->sortable(),
                Tables\Columns\TextColumn::make('subAccount.title')->label('Sub Category ')->sortable(),
                Tables\Columns\TextColumn::make('product_type')->state(function ($record) {
                    if ($record->product_type === 'consumable') {
                        return 'Consumable';
                    } elseif ($record->product_type === 'unConsumable') {
                        return 'Non-Consumable';
                    }
                }),
                Tables\Columns\TextColumn::make('assets_count')->toggleable(true,true)->numeric()->label('Quantity')->badge()
                    ->color(fn($record) => $record->assets->count() > $record->stock_alert_threshold ? 'success' : 'danger')->tooltip(fn($record) => 'Stock Alert:' . $record->stock_alert_threshold),
                Tables\Columns\TextColumn::make('inventories_quantity')->toggleable(true,true)->numeric()->label('Available')->badge(),

                Tables\Columns\TextColumn::make('total_price')->toggleable(true,true)->numeric()->label('Total Value')->badge()->color('success')

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product_type')->options(['consumable'=>'Consumable','unConsumable'=>'Non-Consumable'])->searchable()->preload()

//                SelectFilter::make('unit_id')->searchable()->preload()->options(Unit::where('company_id', getCompany()->id)->get()->pluck('title', 'id'))
//                    ->label('Unit'),

            ], getModelFilter())
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make()->hidden(fn($record)=>$record->purchaseRequestItem()->count() or $record->purchaseOrderItem()->count() or $record->assets()->count() or $record->inventories()->count())

            ])
            ->bulkActions([
                ExportBulkAction::make()
                ->after(function (){
                    if (Auth::check()) {
                        activity()
                            ->causedBy(Auth::user())
                            ->withProperties([
                                'action' => 'export',
                            ])
                            ->log('Export' . "Product");
                    }
                })->exports([
                    ExcelExport::make()->askForFilename("Product")->withColumns([
                        Column::make('sku')->heading('SKU'),
                        Column::make('title')->heading('Product Name'),
                        Column::make('account.title')->heading('Category '),
                        Column::make('subAccount.title')->heading('Sub Category '),
                        Column::make('product_type')->formatStateUsing(function($record){
                            if ($record->product_type==='consumable'){
                                return 'Consumable';
                            }elseif($record->product_type==='unConsumable'){
                                return 'Non-Consumable';
                            }
                        }),
                        Column::make('id')->formatStateUsing(fn($record) => $record->assets->count())->heading('Quantity'),
                        Column::make('created_at')->formatStateUsing(fn($record) => $record->inventories()?->sum('quantity'))->heading('Available'),

                        Column::make('updated_at')->formatStateUsing(fn($record) => $record->assets->sum('price'))->heading('Total Value')
                    ]),
                ])->label('Export Product')->color('purple')
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
