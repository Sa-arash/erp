<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\FactorResource\Pages;
use App\Filament\Admin\Resources\FactorResource\RelationManagers;
use App\Models\Factor;
use App\Models\Parties;
use App\Models\Unit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Hamcrest\Core\Set;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FactorResource extends Resource
{
    protected static ?string $model = Factor::class;
    protected static ?string $label = 'Invoice';
    protected static ?string $pluralLabel = 'Invoices';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
               Forms\Components\Wizard::make([
                   Forms\Components\Wizard\Step::make('Invoice')->schema([
                       Forms\Components\Section::make([
                           Forms\Components\TextInput::make('title')->required()->maxLength(255),
                           Forms\Components\ToggleButtons::make('type')->live()->afterStateUpdated(fn(Forms\Set $set)=>$set('party_id',null))->required()->default(0)->boolean('Income', 'Expense')->grouped(),
                           Forms\Components\Select::make('party_id')->label(fn(Forms\Get $get) => $get('type') === "1" ? "Customer" : "Vendor")->searchable()->required()->options(function (Forms\Get $get) {
                               $type = $get('type') === "1" ? "customer" : "vendor";
                               return getCompany()->parties->whereIn('type', [$type,'both'])->pluck('info', 'id');
                           })->afterStateUpdated(function ($state, Forms\Set $set,Forms\Get $get) {
                               $party= Parties::query()->firstWhere('id',$state);
                               if ($get('type')==="1"){
                                   $set('to',$party?->name);
                               }else{
                                   $set('from',$party?->name);
                               }
                           })->live(true),
                       ])->columns(3),
                       Forms\Components\TextInput::make('from')->required()->maxLength(255),
                       Forms\Components\TextInput::make('to')->required()->maxLength(255),
                       Forms\Components\Repeater::make('items')->required()->relationship('items')->schema([
                           Forms\Components\TextInput::make('title')->required()->label('Title Or Description'),
                           Forms\Components\Select::make('unit_id')->label('Unit')->required()->options(Unit::query()->where('company_id',getCompany()->id)->pluck('title','id'))->searchable()->preload(),
                           Forms\Components\TextInput::make('quantity')->live(true)->required()->label('Quantity')->afterStateUpdated(function (Forms\Get $get,Forms\Set $set){
                               $count=$get('quantity') ===null ? 0 :(int)$get('quantity');
                               $unitPrice=$get('unit_price') ===null ?  0:(int)str_replace(',','',$get('unit_price'));
                               $discount=$get('discount') ===null ?  0:(int)$get('discount');
                               $set('total',number_format(($count*$unitPrice)-(($count*$unitPrice)*$discount)/100));
                           }),
                           Forms\Components\TextInput::make('unit_price')->mask(RawJs::make('$money($input)'))->stripCharacters(',')->live(true)->afterStateUpdated(function (Forms\Get $get,Forms\Set $set){
                               $count=$get('quantity') ===null ? 0 :(int)$get('quantity');
                               $unitPrice=$get('unit_price') ===null ?  0:(int)str_replace(',','',$get('unit_price'));
                               $discount=$get('discount') ===null ?  0:(int)$get('discount');
                               $set('total',number_format(($count*$unitPrice)-(($count*$unitPrice)*$discount)/100));
                           })->required()->label('Unit Price'),
                           Forms\Components\TextInput::make('discount')->live(true)->afterStateUpdated(function (Forms\Get $get,Forms\Set $set){
                               $count=$get('quantity') ===null ? 0 :(int)$get('quantity');
                               $unitPrice=$get('unit_price') ===null ?  0:(int)str_replace(',','',$get('unit_price'));
                               $discount=$get('discount') ===null ?  0:(int)$get('discount');
                               $set('total',number_format(($count*$unitPrice)-(($count*$unitPrice)*$discount)/100));
                           })->default(0)->required()->label('Discount'),
                           Forms\Components\TextInput::make('total')->live()->readOnly()->default(0)->required()->label('Total'),
                       ])->columnSpanFull()->columns(6),
                   ])->columns(2)->afterStateUpdated(function (Forms\Get $get,Forms\Set $set){
                       $total=0;
                       foreach ($get->getData()['items'] as $item){
                           $total+=str_replace(',','',$item['total']);
                       }

                       $set('total',number_format($total));
                   }),
                   Forms\Components\Wizard\Step::make('journal')->label('Journal Entry')->schema([
                       Forms\Components\TextInput::make('total')->afterStateUpdated(function (Forms\Get $get){
                           dd($get->getData('items'));
                       })->required()->numeric()->columnSpanFull(),

                   ])
               ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('party.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('from')
                    ->searchable(),
                Tables\Columns\TextColumn::make('to')
                    ->searchable(),
                Tables\Columns\TextColumn::make('invoice.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('type')
                    ->boolean(),
                Tables\Columns\TextColumn::make('total')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('company.title')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListFactors::route('/'),
            'create' => Pages\CreateFactor::route('/create'),
            'edit' => Pages\EditFactor::route('/{record}/edit'),
        ];
    }
}
