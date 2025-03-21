<?php

namespace App\Filament\Admin\Resources\PurchaseRequestResource\RelationManagers;

use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class ItemsRelationManager extends RelationManager
{
    protected static ?string $label = 'Item';
    protected static string $relationship = 'items';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')->label('Product')->options(function () {
                    $products = getCompany()->products;
                    $data = [];
                    foreach ($products as $product) {
                        $data[$product->id] = $product->title . " (" . $product->sku . ")";
                    }
                    return $data;
                })->required()->searchable()->preload(),

                Forms\Components\TextInput::make('description')
                    ->label('Description')
                    ->required(),

                Forms\Components\Select::make('unit_id')
                    ->searchable()
                    ->preload()
                    ->label('Unit')
                    ->options(getCompany()->units->pluck('title', 'id'))
                    ->required(),
                Forms\Components\TextInput::make('quantity')
                    ->required()
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters(','),

                Forms\Components\TextInput::make('estimated_unit_cost')
                    ->label('Estimated Unit Cost')
                    ->numeric()
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters(','),

                Forms\Components\Select::make('project_id')
                    ->searchable()
                    ->preload()
                    ->label('Project')
                    ->options(getCompany()->projects->pluck('name', 'id')),

                Forms\Components\Hidden::make('company_id')
                    ->default(Filament::getTenant()->id)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('product.sku')->label('SKU'),
                Tables\Columns\TextColumn::make('product.title'),
                Tables\Columns\TextColumn::make('description'),
                Tables\Columns\TextColumn::make('unit.title'),
                Tables\Columns\TextColumn::make('quantity'),
                Tables\Columns\TextColumn::make('estimated_unit_cost')->numeric(),
                Tables\Columns\TextColumn::make('total')->state(fn ($record) => $record->estimated_unit_cost * $record->quantity)->numeric(),
                Tables\Columns\TextColumn::make('project.name'),
                Tables\Columns\TextColumn::make('clarification_decision')->label('Clarification Decision')->alignCenter()->badge(),
                Tables\Columns\TextColumn::make('clarification_comment')->label('Clarification Comment'),
                Tables\Columns\TextColumn::make('verification_decision')->label('Verification Decision')->alignCenter()->badge(),
                Tables\Columns\TextColumn::make('verification_comment')->label('Verification Comment'),
                Tables\Columns\TextColumn::make('approval_decision')->label('Approval Decision')->alignCenter()->badge(),
                Tables\Columns\TextColumn::make('approval_comment')->label('Approval Comment'),
                Tables\Columns\TextColumn::make('status')->badge(),

            ])

            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
//                Tables\Actions\DeleteAction::make(),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
