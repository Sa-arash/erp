<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\VendorTypeResource\Pages;
use App\Filament\Admin\Resources\VendorTypeResource\RelationManagers;
use App\Models\VendorType;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Hamcrest\Core\Set;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VendorTypeResource extends Resource
{
    protected static ?string $model = VendorType::class;
    protected static ?string $navigationGroup = 'Finance Management';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $cluster = \App\Filament\Clusters\AccountSettings::class;
    public static function canAccess(): bool
    {
        return false;
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
               Forms\Components\Section::make([
                   Forms\Components\TextInput::make('title')->required()->maxLength(255),
                   Forms\Components\ToggleButtons::make('type')->colors(['success','danger'])->afterStateUpdated(fn(Forms\Set $set)=> $set('parent_id',null))->inline()->grouped()->boolean('Customer','Vendor')->live(),
                   Forms\Components\Select::make('parent_id')->columnSpanFull()->label('Parent')->searchable()->preload()->options(fn(Forms\Get $get)=>VendorType::query()->where('type',$get('type'))->where('company_id',getCompany()->id)->pluck('title','id')),
                   Forms\Components\Textarea::make('description')   ->maxLength(5000)->columnSpanFull(),
               ])->columns()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('title')->searchable()->alignCenter(),
                Tables\Columns\TextColumn::make('type')->badge()->color(fn($record)=>$record->type ?  "success":'danger')->state(fn($record)=>$record->type ? "Customer" :"Vendor")->searchable()->alignCenter(),
                Tables\Columns\TextColumn::make('children.title')->badge()->alignCenter(),
                Tables\Columns\TextColumn::make('vendors')->label('Vendor/Customer')->url(fn($record) => $record->type ?  CustomerResource::getUrl('index',['tableFilters[vendor_type_id][value]'=>$record->id]) :  VendorResource::getUrl('index',['tableFilters[vendor_type_id][value]'=>$record->id]))->state(fn($record) => $record->type ?   $record->customers->count() : $record->vendors->count() )->badge()->color('aColor')->alignCenter()->sortable(),

            ])
            ->filters([
                //
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
            'index' => Pages\ListVendorTypes::route('/'),
//            'create' => Pages\CreateVendorType::route('/create'),
//            'edit' => Pages\EditVendorType::route('/{record}/edit'),
        ];
    }
}
