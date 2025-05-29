<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\InventoryResource\Pages;
use App\Filament\Admin\Resources\InventoryResource\RelationManagers;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Structure;
use App\Models\Warehouse;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconSize;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class InventoryResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Inventory::class;
    protected static ?string $navigationGroup = 'Logistic Management';
    protected static ?int $navigationSort = 6;
    protected static ?string $navigationIcon = 'heroicon-s-inbox-arrow-down';
    protected static ?string $label="Inventory";
    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'transaction'
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
        ->defaultSort('id', 'desc')->headerActions([
            ExportAction::make()
            ->after(function (){
                if (Auth::check()) {
                    activity()
                        ->causedBy(Auth::user())
                        ->withProperties([
                            'action' => 'export',
                        ])
                        ->log('Export' . "Inventory");
                }
            })->exports([
                ExcelExport::make()->askForFilename("Inventory")->withColumns([
                   Column::make('product.info')->heading('product info'),
                   Column::make('product.unit.title')->heading('product unit title'),
                   Column::make('warehouse.title')->heading('Warehouse'),
                   Column::make('structure')->formatStateUsing(function ($record) {
                        $str = getParents($record->structure);
                        return substr($str, 1, strlen($str) - 1);
                    })->heading('Location'),
                   Column::make('quantity'),
                ]),
            ])->label('Export Inventory')->color('purple')
        ])

        
        
        ->recordUrl(fn($record)=>InventoryResource::getUrl('stocks',['record'=>$record->id]))
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('product.info'),
                Tables\Columns\TextColumn::make('product.unit.title'),
                Tables\Columns\TextColumn::make('warehouse.title')->label('Warehouse'),
                Tables\Columns\TextColumn::make('structure')->state(function ($record) {
                    $str = getParents($record->structure);
                    return substr($str, 1, strlen($str) - 1);
                })->label('Location'),
                Tables\Columns\TextColumn::make('quantity')->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product_id')->label('Product')->options(getCompany()->products()->where('product_type', 'consumable')->pluck('title', 'id'))->searchable()->preload()->getSearchResultsUsing(fn (string $search,Get $get): array => Product::query()->where('company_id',getCompany()->id)->where('title','like',"%{$search}%")->orWhere('second_title','like',"%{$search}%")->pluck('title', 'id')->toArray())->getOptionLabelsUsing(function(array $values){
                    $data=[];
                    $products=getCompany()->products->whereIn('id', $values)->pluck('title', 'id');
                    $i=1;
                    foreach ($products as $key=> $product){
                        $data[$key]=$i.". ". $product;
                        $i++;
                    }
                    return $data ;

                }),
                Tables\Filters\SelectFilter::make('warehouse_id')->label('Warehouse')->options(getCompany()->warehouses()->where('type',1)->pluck('title','id'))->searchable()->preload()
            ],getModelFilter())
            ->actions([
                Tables\Actions\Action::make('Transaction')->iconSize(IconSize::Medium)->form(function ($record){
                    return [
                        Section::make([
                            TextInput::make('quantity')->minValue(1)->required()->numeric(),
                            Select::make('warehouse_id')->required()->live(true)->label('Warehouse')->options(getCompany()->warehouses()->where('type',1)->pluck('title','id'))->searchable()->preload(),
                            SelectTree::make('structure_id')->hidden(function (Get $get)use($record){

                                return Inventory::query()->where('warehouse_id',$get('warehouse_id'))->where('product_id',$record->product_id)->first();

                            })->label('Location')->enableBranchNode()->defaultOpenLevel(2)->model(Structure::class)->relationship('parent', 'title', 'parent_id', modifyQueryUsing: function ($query,Get $get) {
                                return $query->where('warehouse_id', $get('warehouse_id'));
                            })->required(),
                            Textarea::make('description')->required()->maxLength(255)->columnSpanFull(),
                        ])->columns(3)
                    ];
                })->action(function ($data,$record){

                    if (!($record->quantity-$data['quantity'] >=0)){
                        Notification::make('error')->warning()->title('Quantity Not Valid')->send();
                        return;
                    }
                   $inventory= Inventory::query()->where('warehouse_id',$data['warehouse_id'])->where('product_id',$record->product_id)->first();
                    if (!$inventory){
                        if (isset($data['structure_id'])){
                            $inventory= Inventory::query()->create([
                                'warehouse_id'=>$data['warehouse_id'],
                                'product_id'=>$record->product_id,
                                'quantity'=>0,
                                'structure_id'=>$data['structure_id'],
                                'company_id'=>$record->company_id
                            ]);
                        }
                    }
                    Stock::query()->create([
                        'inventory_id'=>$record->id,
                        'employee_id'=>getEmployee()->id,
                        'quantity'=>$data['quantity'],
                        'description'=>$data['description'],
                        'type'=>0,
                        'transaction'=>1
                    ]);
                    $record->update(['quantity'=>$record->quantity-$data['quantity']]);
                    Stock::query()->create([
                        'inventory_id'=>$inventory->id,
                        'employee_id'=>getEmployee()->id,
                        'quantity'=>$data['quantity'],
                        'description'=>$data['description'],
                        'type'=>1,
                        'transaction'=>1
                    ]);
                    $inventory->update(['quantity'=>$inventory->quantity+$data['quantity']]);
                    Notification::make('success')->success()->title('Successfully')->send();

                })->requiresConfirmation()->modalWidth(MaxWidth::FiveExtraLarge)->icon('heroicon-c-arrow-up-tray')->modalIcon('heroicon-c-arrow-up-tray')->color('warning'),
                Tables\Actions\Action::make('stocks')->url(fn($record)=>InventoryResource::getUrl('stocks',['record'=>$record->id]))
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
                            ->log('Export' . "Inventory");
                    }
                })->exports([
                    ExcelExport::make()->askForFilename("Inventory")->withColumns([
                       Column::make('product.info')->heading('product info'),
                       Column::make('product.unit.title')->heading('product unit title'),
                       Column::make('warehouse.title')->heading('Warehouse'),
                       Column::make('structure')->formatStateUsing(function ($record) {
                            $str = getParents($record->structure);
                            return substr($str, 1, strlen($str) - 1);
                        })->heading('Location'),
                       Column::make('quantity'),
                    ]),
                ])->label('Export Inventory')->color('purple')
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
            'index' => Pages\ListInventories::route('/'),
            'stocks'=>Pages\Stocks::route('/{record}/stocks')
//            'create' => Pages\CreateInventory::route('/create'),
//            'edit' => Pages\EditInventory::route('/{record}/edit'),
        ];
    }
}
