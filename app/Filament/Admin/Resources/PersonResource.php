<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PersonResource\Pages;
use App\Models\Person;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use TomatoPHP\FilamentMediaManager\Form\MediaManagerInput;

class PersonResource extends Resource
{
    protected static ?string $model = Person::class;
    protected static ?string $navigationGroup = 'Logistic Management';
    protected static ?int $navigationSort=8;

    protected static ?string $navigationIcon = 'heroicon-s-user-group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                MediaManagerInput::make('image')->orderable(false)->folderTitleFieldName("name")->image(true)
                    ->disk('public')
                    ->schema([])->maxItems(1)->columnSpanFull(),
                Forms\Components\TextInput::make('name')->required()->maxLength(255),
                Forms\Components\TextInput::make('number')->default(function () {
                $lastPerson = Person::query()->where('company_id', getCompany()->id)->latest()->first();
                    if ($lastPerson) {
                        return getNextCodePerson($lastPerson->number, 'PSN');
                    } else {
                        return 'PSN00001';
                    }
                })->readOnly()->required()->maxLength(255),
                Forms\Components\Select::make('person_group')->options(getCompany()->person_group)
                    ->createOptionForm([
                        Forms\Components\TextInput::make('title')->required()
                    ])->createOptionUsing(function ($data) {
                        $array = getCompany()->person_group;
                        if (isset($array)) {
                            $array[$data['title']] = $data['title'];
                        } else {
                            $array = [$data['title'] => $data['title']];
                        }
                        getCompany()->update(['person_group' => $array]);
                        return $data['title'];
                    })->searchable(),
                Forms\Components\TextInput::make('job_title')->maxLength(255)->default(null),
                Forms\Components\Section::make([
                    Forms\Components\TextInput::make('work_phone')->maxLength(255)->default(null),
                    Forms\Components\TextInput::make('home_phone')->maxLength(255)->default(null),
                    Forms\Components\TextInput::make('mobile_phone')->tel()->maxLength(255)->default(null),
                ])->columns(3),
                Forms\Components\TextInput::make('pager')->maxLength(255)->default(null),
                Forms\Components\TextInput::make('email')->email()->maxLength(255)->default(null),
                Forms\Components\Textarea::make('note')->columnSpanFull(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('#')->rowIndex(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('person_group')->searchable(),
                Tables\Columns\TextColumn::make('number')->searchable(),
                Tables\Columns\TextColumn::make('job_title')->searchable(),
                Tables\Columns\TextColumn::make('work_phone')->searchable(),
                Tables\Columns\TextColumn::make('home_phone')->searchable(),
                Tables\Columns\TextColumn::make('mobile_phone')->searchable(),
                Tables\Columns\TextColumn::make('pager')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPeople::route('/'),
            'create' => Pages\CreatePerson::route('/create'),
            'edit' => Pages\EditPerson::route('/{record}/edit'),
        ];
    }
}
