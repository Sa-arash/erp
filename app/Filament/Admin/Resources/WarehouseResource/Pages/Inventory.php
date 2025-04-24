<?php

namespace App\Filament\Admin\Resources\WarehouseResource\Pages;

use App\Filament\Admin\Resources\WarehouseResource;
use App\Models\Product;
use App\Models\Structure;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Session;

class Inventory extends ManageRelatedRecords
{
    protected static string $resource = WarehouseResource::class;
    protected static string $relationship = 'inventories';
    protected static ?string $navigationIcon = 'heroicon-s-inbox-arrow-down';
    public static function getNavigationLabel(): string
    {
        return 'Inventories';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')->options(getCompany()->products()->where('product_type', 'consumable')->pluck('title', 'id'))->searchable()->preload()->label('Product')->required()->getSearchResultsUsing(fn (string $search,Get $get): array => Product::query()->where('company_id',getCompany()->id)->where('title','like',"%{$search}%")->orWhere('second_title','like',"%{$search}%")->pluck('title', 'id')->toArray())->getOptionLabelsUsing(function(array $values){
                    $data=[];
                    $products=getCompany()->products->whereIn('id', $values)->pluck('title', 'id');
                    $i=1;
                    foreach ($products as $key=> $product){
                        $data[$key]=$i.". ". $product;
                        $i++;
                    }
                    return $data ;

                }),
                SelectTree::make('structure_id')->label('Location')->enableBranchNode()->defaultOpenLevel(2)->model(Structure::class)->relationship('parent', 'title', 'parent_id', modifyQueryUsing: function ($query) {
                    return $query->where('warehouse_id', $this->record->id);
                })->required(),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('product.info'),
                Tables\Columns\TextColumn::make('product.unit.title'),
                Tables\Columns\TextColumn::make('structure')->state(function ($record) {
                    $str = getParents($record->structure);
                    return substr($str, 1, strlen($str) - 1);
                })->label('Location'),
                Tables\Columns\TextColumn::make('quantity')->badge(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->action(function ($data) {
                    \App\Models\Inventory::query()->create([
                        'warehouse_id' => $this->record->id,
                        'product_id' => $data['product_id'],
                        'structure_id' => $data['structure_id'],
                        'quantity' => 0,
                        'company_id' => getCompany()->id,
                    ]);
                    Notification::make('success')->success()->title('Added')->send();
                }),

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('Stock')->url(function($record){
                    Session::push('inventoryID',$record->id);
                    return WarehouseResource::getUrl('stock',['record'=>$this->record->id,'inventory'=>$record->id]);
                })
            ]);
    }

}
