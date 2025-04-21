<?php

namespace App\Filament\Admin\Resources\LocationResource\RelationManagers;

use App\Filament\Admin\Resources\AssetResource;
use App\Models\Structure;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StructuresRelationManager extends RelationManager
{
    protected static string $relationship = 'structures';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make([
                    Forms\Components\TextInput::make('title')->required()->maxLength(255),
                    SelectTree::make('parent_id')->label('Parent')->enableBranchNode()->defaultOpenLevel(2)->model(Structure::class)->relationship('parent', 'title', 'parent_id',modifyQueryUsing: function($query,Get $get){
                        return $query->where('warehouse_id', $this->ownerRecord->id)->where('location',$get('location'));
                    }),
                ])->columns(1)
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')->state(function ($record) {
                    $title = addSpacesBasedOnParentLevel($record) . $record->title;
                    return "<pre style='font-family: Arial,serif'>  $title</pre>";
                })->html(),
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('Employees')->state(fn($record) => $record->employees->count())->badge()
//                    ->url(fn($record) => AssetResource::getUrl('index', ['tableFilters[tree][structure_id]' => $record->id])),
            ,
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
                })
                    ->circular()
                    ->stacked()
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->form(function () {
                    return [
                        Forms\Components\TextInput::make('title')->required()->maxLength(255),
                        SelectTree::make('parent_id')->label('Parent')->enableBranchNode()->defaultOpenLevel(2)->model(Structure::class)->relationship('parent', 'title', 'parent_id',modifyQueryUsing: function($query,Get $get){
                            return $query->where('warehouse_id', $this->ownerRecord->id);
                        }),
                    ];
                })->action(function ($data) {
                    Structure::query()->create([
                        'title' => $data['title'],
                        'parent_id' => $data['parent_id'],
                        'warehouse_id' => $this->ownerRecord->id,
                        'location'=>1,
                        'company_id' => getCompany()->id,
                    ]);
                    Notification::make('save')->success()->title('Save ')->send();
                }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->hidden(fn($record)=>$record->chiller->count() !==0 or $record->assets->count()!==0 or $record->employees->count()!==0),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
