<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ProductCategoryResource\Pages;
use App\Filament\Admin\Resources\ProductCategoryResource\RelationManagers;
use App\Filament\Clusters\StackManagementSettings;
use App\Models\ProductCategory;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductCategoryResource extends Resource
{
    protected static ?string $model = ProductCategory::class;

    protected static ?string $navigationIcon = 'heroicon-s-squares-2x2';
    protected static ?string $navigationGroup = 'Logistic Management';
    protected static ?string $cluster = StackManagementSettings::class;
    protected static ?string $label="Product Group";
    protected static ?string $pluralLabel="Product Groups";




    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('code')->default(function (){
                    return ProductCategory::query()->where('parent_id',null)->orderBy("id",'desc')->first()?->generateCodeFromParent() ??"01";
                })->readOnly()
                    ->required()
                    ->maxLength(255),
                SelectTree::make('parent_id')->defaultOpenLevel(2)->enableBranchNode()->searchable()->live()->label('Parent')->columnSpanFull()->relationship('parent', 'title', 'parent_id', modifyQueryUsing: fn($query) => $query->where('company_id', getCompany()->id), modifyChildQueryUsing: fn($query) => $query->where('company_id', getCompany()->id))->afterStateUpdated(function (Forms\Set $set,$state){
                    if ($state){
                        $set('code',ProductCategory::query()->firstWhere('id',$state)?->generateNextChildCode());
                    }else{
                        $set( 'code',ProductCategory::query()->where('parent_id',null)->orderBy("id",'desc')->first()?->generateCodeFromParent() ??"01");
                    }
                }),
            ]);
    }
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['products'])->withCount('products');
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort("code")
            ->columns([
                Tables\Columns\TextColumn::make(getRowIndexName())->rowIndex(),
                Tables\Columns\TextColumn::make('title')->state(function ($record){
                    $space="";
                    for ($i=1;$i<=strlen($record->code)*4;$i++){
                        $space.= "&nbsp;";
                    }
                    return $space.=$record->title." (". $record->code.")";
                })->searchable()->html()->searchable()->html()->color(function ($record){
                    return match (strlen($record->code)*4){
                        8=>"success",
                        16=>"warning",
                        24=>"aColor",
                        32=>"info",
                        default => "danger"
                    };
                }),
                Tables\Columns\TextColumn::make('parent.title'),
                Tables\Columns\TextColumn::make('products_count')->badge()->numeric()->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->hidden(fn($record)=>$record->children()->count() or $record->products()->count())
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
            'index' => Pages\ListProductCategories::route('/'),
//            'create' => Pages\CreateProductCategory::route('/create'),
//            'edit' => Pages\EditProductCategory::route('/{record}/edit'),
        ];
    }
}
