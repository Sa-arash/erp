<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\VisitorRequestResource\Pages;
use App\Filament\Admin\Resources\VisitorRequestResource\RelationManagers;
use App\Models\VisitorRequest;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconSize;
use Filament\Tables;
use Filament\Tables\Table;

class VisitorRequestResource extends Resource
{
    protected static ?string $model = VisitorRequest::class;
    protected static ?string $navigationLabel = 'Visit Access Request';
    protected static ?string $navigationGroup = 'Security Management';
    protected static ?int $navigationSort = 0;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Visitor Access Request')->schema([
                    Section::make('Visit’s Details')->schema([
                        Forms\Components\Select::make('requested_by')->live()
                            ->searchable()
                            ->preload()
                            ->required()
                            ->options(getCompany()->employees->pluck('fullName', 'id'))
                            ->default(fn() => auth()->user()->employee->id),

                            Forms\Components\DatePicker::make('visit_date')->default(now()->addDay())

                            ->required(),


                        Forms\Components\TimePicker::make('arrival_time')
                        ->seconds(false)
                        ->before('departure_time')
                            ->required(),
                        Forms\Components\TimePicker::make('departure_time')
                        ->seconds(false)
                        ->after('arrival_time')
                            ->required(),
                        Forms\Components\TextInput::make('purpose')->columnSpanFull()
                        ->required(),
                        ])->columns(4),
                    Forms\Components\Repeater::make('visitors_detail')
                    ->addActionLabel('Add')
                    ->label('Visitors Detail')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                        ->label('Full Name')
                            ->required(),
                        Forms\Components\TextInput::make('id')
                            ->label('ID/Passport')
                            ->required(),
                        Forms\Components\TextInput::make('phone')
                            ->label('Phone'),
                        Forms\Components\TextInput::make('organization')
                            ->label('Organization'),
                        Forms\Components\Select::make('type')
                            ->label('Type')
                            ->options([
                                'National' => 'National',
                                'International' => 'International',
                                'De-facto Security Forces' => 'De-facto Security Forces',
                            ]),
                        Forms\Components\TextInput::make('remarks')
                            ->label('Remarks'),

                    ])->columns(6)->columnSpanFull(),




                    Forms\Components\Repeater::make('driver_vehicle_detail')
                    ->addActionLabel('Add')
                    ->label('Drivers/Vehicles Detail')->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Full Name')
                            ->required(),
                        Forms\Components\TextInput::make('id')
                            ->label('ID/Passport')
                            ->required(),
                        Forms\Components\TextInput::make('phone')
                            ->label('Phone'),
                            Forms\Components\TextInput::make('model')
                            ->required(),
                        Forms\Components\TextInput::make('color')
                            ->required(),
                        Forms\Components\TextInput::make('Registration_Plate')
                            ->required(),

                    ])->columns(6)->columnSpanFull(),













                    Forms\Components\Hidden::make('company_id')
                        ->default(getCompany()->id)
                        ->required(),
                ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('requested.fullName')
                ->label('Requestor')
                    ->numeric()
                    ->sortable(),
                    Tables\Columns\TextColumn::make('visitors_detail')
                    ->label('Visitors')
                    ->state(fn($record)=>implode(', ',(array_map(fn($item)=>$item['name'],$record->visitors_detail))))
                        ->numeric()
                        ->sortable(),
                    Tables\Columns\TextColumn::make('visit_date')
                        ->date()
                        ->sortable(),

                Tables\Columns\TextColumn::make('arrival_time'),
                Tables\Columns\TextColumn::make('departure_time'),



                Tables\Columns\TextColumn::make('status'),

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
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('pdf')->tooltip('Print')->icon('heroicon-s-printer')->iconSize(IconSize::Medium)->label('')
                    ->url(fn($record) => route('pdf.requestVisit', ['id' => $record->id]))->openUrlInNewTab(),

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

    public static function getForm()
    {
        return [
            Section::make('Visitor Access Request')->schema([
                Section::make('Visit’s Details')->schema([
                    Forms\Components\DatePicker::make('visit_date')->default(now()->addDay())->required(),
                    Forms\Components\TimePicker::make('arrival_time')->seconds(false)->before('departure_time')->required(),
                    Forms\Components\TimePicker::make('departure_time')->seconds(false)->after('arrival_time')->required(),
                    Forms\Components\TextInput::make('purpose')->columnSpanFull()
                        ->required(),
                ])->columns(4),
                Forms\Components\Repeater::make('visitors_detail')
                    ->addActionLabel('Add')
                    ->label('Visitors Detail')
                    ->schema([
                        Forms\Components\TextInput::make('name')->label('Full Name')->required(),
                        Forms\Components\TextInput::make('id')->label('ID/Passport')->required(),
                        Forms\Components\TextInput::make('phone')->label('Phone'),
                        Forms\Components\TextInput::make('organization')->label('Organization'),
                        Forms\Components\Select::make('type')->label('Type')->options(['National' => 'National', 'International' => 'International', 'De-facto Security Forces' => 'De-facto Security Forces',]),
                        Forms\Components\TextInput::make('remarks')->label('Remarks'),
                    ])->columns(6)->columnSpanFull(),
                Forms\Components\Repeater::make('driver_vehicle_detail')
                    ->addActionLabel('Add')
                    ->label('Drivers/Vehicles Detail')->schema([
                        Forms\Components\TextInput::make('name')->label('Full Name')->required(),
                        Forms\Components\TextInput::make('id')->label('ID/Passport')->required(),
                        Forms\Components\TextInput::make('phone')->label('Phone'),
                        Forms\Components\TextInput::make('model')->label('Vehicles Model')->required(),

                        Forms\Components\TextInput::make('color')->label('Vehicles Color')->required(),
                        Forms\Components\TextInput::make('Registration_Plate')->required(),
                    ])->columns(6)->columnSpanFull(),
            ])->columns(2)
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVisitorRequests::route('/'),
            'create' => Pages\CreateVisitorRequest::route('/create'),
            'edit' => Pages\EditVisitorRequest::route('/{record}/edit'),
        ];
    }
}
