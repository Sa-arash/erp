<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ProjectResource\Pages;
use App\Filament\Admin\Resources\ProjectResource\RelationManagers;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;
    protected static ?int $navigationSort=2;

    protected static ?string $navigationIcon = 'heroicon-o-squares-plus';
    protected static ?string $navigationGroup = 'Finance Management';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required()->maxLength(255),
                Forms\Components\TextInput::make('code')->required()->maxLength(255),
                Forms\Components\DatePicker::make('start_date'),
                Forms\Components\DatePicker::make('end_date')->afterOrEqual(fn(Forms\Get $get)=>$get('start_date')),
                Forms\Components\Textarea::make('description')->columnSpanFull(),
                Forms\Components\Select::make('employee_id')->label('Project Manager')->options(getCompany()->employees()->pluck('fullName','id'))->searchable()->required(),
                Forms\Components\Select::make('members')->label('Team Members')->options(getCompany()->employees()->pluck('fullName','id'))->searchable()->preload(),
                Forms\Components\Select::make('priority_level')->searchable()->options(['High'=>'High','Medium'=>'Medium','Low'=>'Low'])->required(),
                Forms\Components\TextInput::make('budget')->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)->numeric(),
                Forms\Components\FileUpload::make('files')->multiple()->downloadable()->columnSpanFull(),
                Forms\Components\TagsInput::make('tags')->columnSpanFull(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('code')->searchable(),
                Tables\Columns\TextColumn::make('start_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('end_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('employee.fullName')->label('Manager')->sortable(),
                Tables\Columns\TextColumn::make('priority_level')->badge(),
                Tables\Columns\TextColumn::make('budget')->numeric()->sortable(),
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
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
