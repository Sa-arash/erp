<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\LocationResource\Pages;
use App\Filament\Admin\Resources\LocationResource\RelationManagers;
use App\Filament\Clusters\HrSettings;
use App\Models\Structure;
use App\Models\Warehouse;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LocationResource extends Resource
{
    protected static ?string $model = Warehouse::class;
    protected static ?string $label="Location";
    protected static ?string $navigationGroup = 'HR Management System';
    protected static ?string $navigationIcon = 'heroicon-o-home-modern';
    protected static ?string $cluster = HrSettings::class;
    protected static ?int $navigationSort=10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')->columnSpanFull()->label('Main Location')->required()->maxLength(255),
                Forms\Components\Hidden::make('type')->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->query(Warehouse::query()->where('type',0)->where('company_id',getCompany()->id))
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('title')->label('Main Location')->searchable(),
                Tables\Columns\TextColumn::make('structures.title')->label('Sub Location')->badge()->searchable(),
                Tables\Columns\TextColumn::make('employees')->state(fn($record)=>$record->employees->count())->label('Employees')->badge()->searchable(),
                Tables\Columns\ImageColumn::make('employees.medias')->label('Employees Photo')->state(function ($record){
                    $data=[];
                    foreach ($record->employees as $employee){
                        if ($employee->media->where('collection_name','images')->first()?->original_url){
                            $data[]= $employee->media->where('collection_name','images')->first()?->original_url;
                        } else {
                            $data[] = $employee->gender === "male" ? asset('img/user.png') : asset('img/female.png');
                        }
                    }
                    return $data;
                })->circular()->stacked(),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('add')->label('Add Sub Location')->form(function ($record){
                    return [
                        Forms\Components\TextInput::make('title')->required()->maxLength(255),
                        SelectTree::make('parent_id')->label('Parent')->enableBranchNode()->defaultOpenLevel(2)->model(Structure::class)->relationship('parent', 'title', 'parent_id',modifyQueryUsing: function($query,Get $get)use($record){
                            return $query->where('warehouse_id', $record->id);
                        }),
                    ];
                })->action(function ($data,$record) {
                    Structure::query()->create([
                        'title'=>$data['title'],
                        'parent_id'=>$data['parent_id'],
                        'warehouse_id'=>$record->id,
                        'location'=>1,
                        'company_id'=>getCompany()->id,
                    ]);
                    Notification::make('save')->success()->title('Save ')->send();
                }),
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
            'index' => Pages\ListLocations::route('/'),
            'edit' => Pages\EditLocation::route('/{record}/edit'),
        ];
    }
}
