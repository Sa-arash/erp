<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\WarehouseResource\Pages;
use App\Filament\Admin\Resources\WarehouseResource\RelationManagers;
use App\Filament\Clusters\StackManagementSettings;
use App\Models\Employee;
use App\Models\Structure;
use App\Models\Warehouse;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use function Laravel\Prompts\select;

class WarehouseResource extends Resource
{
    protected static ?string $model = Warehouse::class;
    protected static ?string $navigationGroup = 'Logistic Management';
    protected static ?string $navigationIcon = 'heroicon-c-home-modern';
    protected static ?string $cluster = StackManagementSettings::class;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')->label('Warehouse Name')
                    ->required()
                    ->maxLength(255),
                Select::make('employee_id')->required()->label('Manager')
                    ->searchable()
                    ->preload()
                    ->options(getCompany()->employees()->get()->pluck('fullName', 'id')),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->maxLength(255),
                Forms\Components\Select::make('country')
                    ->options(getCountry())->searchable()->preload(),
                Forms\Components\TextInput::make('state')->label('State/Province')
                    ->maxLength(255),
                Forms\Components\TextInput::make('city')
                    ->maxLength(255),
                Forms\Components\Textarea::make('address')->maxLength(255)->columnSpanFull(),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('title')->label('Warehouse Name')->searchable(),
                Tables\Columns\TextColumn::make('employee.fullName')->label('Manager')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('phone')->searchable(),
                Tables\Columns\TextColumn::make('country')->searchable(),
                Tables\Columns\TextColumn::make('state')->searchable(),
                Tables\Columns\TextColumn::make('city')->searchable(),
                Tables\Columns\TextColumn::make('address')->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('add')->label('Add Structure')->form(function ($record){
                    return [
                        Forms\Components\TextInput::make('title')->required()->maxLength(255),
                        Forms\Components\ToggleButtons::make('location')->live()->grouped()->required()->default(0)->boolean('Building','Warehouse'),
                        SelectTree::make('parent_id')->label('Parent')->enableBranchNode()->defaultOpenLevel(2)->model(Structure::class)->relationship('parent', 'title', 'parent_id',modifyQueryUsing: function($query,Get $get)use($record){
                            return $query->where('warehouse_id', $record->id)->where('location',$get('location'));
                        }),
                        Select::make('type')->label('Type')->live()->options(['aisle' => "Aisle", 'room' => 'Room', 'shelf' => "Shelf", 'row' => "Row"])->searchable()->preload()->required()
                    ];
                })->action(function ($data,$record) {
                    Structure::query()->create([
                            'title'=>$data['title'],
                            'parent_id'=>$data['parent_id'],
                            'warehouse_id'=>$record->id,
                            'type'=>$data['type'],
                            'location'=>$data['location'],
                            'company_id'=>getCompany()->id,
                        ]);
                    Notification::make('save')->success()->title('Save ')->send();
                })
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
            RelationManagers\StructuresRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWarehouses::route('/'),
            'create' => Pages\CreateWarehouse::route('/create'),
            'edit' => Pages\EditWarehouse::route('/{record}/edit'),
        ];
    }
}
