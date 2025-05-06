<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ProjectResource\Pages;
use App\Filament\Admin\Resources\ProjectResource\RelationManagers;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use TomatoPHP\FilamentMediaManager\Form\MediaManagerInput;

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
               Forms\Components\Section::make([
                   Forms\Components\TextInput::make('name')->columnSpan(2)->required()->maxLength(255),
                   Forms\Components\TextInput::make('code')->default(function(){
                    $maxCode = Project::orderBy('code', 'desc')->value('code');

                    if ($maxCode) {
                        $parts = explode('-', $maxCode);
                        $numberPart = (int)$parts[1];
                        $nextNumberPart = str_pad($numberPart + 1, 4, '0', STR_PAD_LEFT);
                        $newCode = $parts[0] . '-' . $nextNumberPart;
                    } else {
                        $newCode = '2025-0001';
                    }
                    
                    return $newCode;
                   })->required()->maxLength(255),
                   Forms\Components\DatePicker::make('start_date'),
                   Forms\Components\DatePicker::make('end_date')->afterOrEqual(fn(Forms\Get $get)=>$get('start_date')),
               ])->columns(5),
                Forms\Components\Section::make([
                    Forms\Components\Select::make('employee_id')->label('Project Manager')->options(getCompany()->employees()->pluck('fullName','id'))->searchable()->required(),
                    Forms\Components\Select::make('members')->label('Team Members')->multiple()->options(getCompany()->employees()->pluck('fullName','id'))->searchable()->preload(),
                    Forms\Components\Select::make('priority_level')->label('Priority Level')->searchable()->options(['High'=>'High','Medium'=>'Medium','Low'=>'Low']),
                    Forms\Components\TextInput::make('budget')->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)->numeric(),
                ])->columns(4),
                Forms\Components\Textarea::make('description')->columnSpanFull(),
                Forms\Components\TagsInput::make('tags')->columnSpanFull(),
                MediaManagerInput::make('document')->orderable(false)->folderTitleFieldName("name")
                    ->disk('public')
                    ->schema([
                    ])->columnSpanFull()->grid()->defaultItems(0),

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
                Tables\Actions\Action::make('Supplies')->label('Supplies')->infolist(function ($record){
                  return  [
                      RepeatableEntry::make('purchaseRequestItem')->schema([
                          TextEntry::make('product.title'),
                          TextEntry::make('status')->badge(),
                      ])->columns(4)
                  ];
                })
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
