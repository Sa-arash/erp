<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\StructureResource\Pages;
use App\Filament\Admin\Resources\StructureResource\RelationManagers;
use App\Models\Structure;
use App\Models\Warehouse;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StructureResource extends Resource
{
    protected static ?string $model = Structure::class;
    protected static ?string $navigationGroup = 'Logistic management';
    protected static ?string $navigationIcon = 'heroicon-m-square-3-stack-3d';
    public static function canAccess(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                // ->label('Row')
                    ->required()
                    ->maxLength(255),
                Select::make('parent_id')
                // ->label('Rack/Shelf')
                    ->options(fn(Get $get)=>getCompany()->structures()->where('warehouse_id',$get('warehouse_id'))->get()->pluck('title', 'id'))
                    ->searchable()->preload(),
                Select::make('warehouse_id')->label('Warehouse')->live()
                    ->options(getCompany()->warehouses()->get()->pluck('title', 'id'))
                    ->searchable()->preload()
                    ->required()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Select::make('employee_id')->label('Manager')
                            ->searchable()
                            ->preload()
                            ->options(getCompany()->employees()->get()->pluck('fullName', 'id')),
                        Forms\Components\TextInput::make('phone')->tel()->maxLength(255),
                        Forms\Components\Select::make('country')->options(getCountry())->searchable()->preload(),
                        Forms\Components\TextInput::make('state')->label('State/Province')->maxLength(255),
                        Forms\Components\TextInput::make('city')->maxLength(255),
                        Forms\Components\Textarea::make('address')->maxLength(255)->columnSpanFull(),
                        Forms\Components\Hidden::make('company_id')->default(getCompany()->id)->required(),
                    ])->createOptionUsing(function (array $data) {
                        return Warehouse::query()->create([
                            'name' => $data['name'],
                            'employee_id' => $data['employee_id'],
                            'phone' => $data['phone'],
                            'country' => $data['country'],
                            'state' => $data['state'],
                            'city' => $data['city'],
                            'address' => $data['address'],
                            'company_id' => $data['company_id'] = getCompany()->id
                        ])->getKey();
                    }),
                Forms\Components\Hidden::make('company_id')->default(getCompany()->id)->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('parent.title')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('warehouse.title')
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
            'index' => Pages\ListStructures::route('/'),
            'create' => Pages\CreateStructure::route('/create'),
            'edit' => Pages\EditStructure::route('/{record}/edit'),
        ];
    }
}
