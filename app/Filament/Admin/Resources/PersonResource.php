<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PersonResource\Pages;
use App\Models\Person;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconSize;
use Filament\Tables;
use Filament\Tables\Table;
use TomatoPHP\FilamentMediaManager\Form\MediaManagerInput;

class PersonResource extends Resource
{
    protected static ?string $model = Person::class;
    protected static ?string $navigationGroup = 'Logistic Management';
    protected static ?string $label = 'Personnel';
    protected static ?int $navigationSort = 8;

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
                Forms\Components\Section::make()->schema([Forms\Components\Select::make('person_group')->label('Group')->options(getCompany()->person_grope)
                    ->createOptionForm([
                        Forms\Components\TextInput::make('title')->required()
                    ])->createOptionUsing(function ($data) {
                        $array = getCompany()->person_grope;
                        if (isset($array)) {
                            $array[$data['title']] = $data['title'];
                        } else {
                            $array = [$data['title'] => $data['title']];
                        }
                        getCompany()->update(['person_grope' => $array]);
                        return $data['title'];
                    })->searchable(),
                    Forms\Components\TextInput::make('job_title')->maxLength(255)->default(null),
                    Forms\Components\ToggleButtons::make('status')->default(1)->boolean('Active','Inactive')->grouped()
                    ])->columns(3),
                Forms\Components\Section::make([
                    Forms\Components\TextInput::make('work_phone')->maxLength(255)->default(null),
                    Forms\Components\TextInput::make('home_phone')->maxLength(255)->default(null),
                    Forms\Components\TextInput::make('mobile_phone')->tel()->maxLength(255)->default(null),
                ])->columns(3),
                Forms\Components\TextInput::make('pager')->maxLength(255)->default(null),
                Forms\Components\TextInput::make('email')->email()->maxLength(255)->default(null),
                Forms\Components\Textarea::make('note')->columnSpanFull(),
                MediaManagerInput::make('attachment')->orderable(false)->folderTitleFieldName("name")->image(true)
                    ->disk('public')->columns()
                    ->schema([
                        Forms\Components\Textarea::make('description')->required()
                    ])->columnSpanFull()->addActionLabel('Add To Attachment'),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make(getRowIndexName())->rowIndex(),
                Tables\Columns\ImageColumn::make('media.original_url')->width(70)->height(70)->searchable(),
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
                Tables\Actions\Action::make('pdf')->visible(fn($record)=>$record->assetEmployee->count())->tooltip('Print History')->icon('heroicon-s-printer')->iconSize(IconSize::Medium)->label('Print History')->url(fn($record) => route('pdf.employeeAssetHistory', ['id' => $record->id,'type'=>'Personnel','company'=>$record->company_id]))->openUrlInNewTab(),

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
