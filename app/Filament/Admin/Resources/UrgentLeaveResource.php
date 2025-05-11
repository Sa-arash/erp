<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UrgentLeaveResource\Pages;
use App\Filament\Admin\Resources\UrgentLeaveResource\RelationManagers;
use App\Models\Employee;
use App\Models\UrgentLeave;
use App\Models\UrgentTypeleave;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UrgentLeaveResource extends Resource
{
    protected static ?string $model = UrgentLeave::class;

    protected static ?string $navigationGroup = 'HR Management System';
    protected static ?int $navigationSort = 3;
    protected static ?string $pluralLabel = "Urgent Leave ";
    protected static ?string $label = "Urgent Leave";
    protected static ?string $navigationIcon = 'heroicon-o-folder-minus';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('employee_id')->label('Employee')->required()->options(Employee::query()->where('company_id', getCompany()->id)->pluck('fullName', 'id'))->searchable()->preload(),


                Forms\Components\Select::make('urgent_typeleave_id')
                    ->createOptionForm([
                        Forms\Components\TextInput::make('title')->maxLength(250)->required(),
                        Forms\Components\TextInput::make('hours')->label('Max Hours')->numeric()->required(),
                        Forms\Components\ToggleButtons::make('is_payroll')->inline()->boolean('Paid Leave', 'Unpaid Leave')->label('Payment')->required(),
                        Forms\Components\Textarea::make('description')->nullable()->maxLength(255)->columnSpanFull()
                    ])
                    ->createOptionUsing(function (array $data): int {
                        return UrgentTypeleave::query()->create([
                            'title' => $data['title'],
                            'hours' => $data['hours'],
                            'is_payroll' => $data['is_payroll'],
                            'description' => $data['description'],
                            'company_id' => getCompany()->id
                        ])->getKey();
                    })->label('Urgent Leave Type')->required()->options(UrgentTypeleave::query()->where('company_id', getCompany()->id)->pluck('title', 'id'))->searchable()->preload(),


                Forms\Components\TimePicker::make('time_out')
                    ->before('time_in')
                    ->seconds(false)
                    ->reactive()
                    ->afterStateUpdated(function (Set $set, $state) {
                        $set('time_in', $state);
                    })
                    ->required(),
                Forms\Components\TimePicker::make('time_in')
                    ->after('time_out')
                    ->seconds(false),

                Forms\Components\TextInput::make('hours')->numeric()
                    ->reactive()
                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                        if ($get('time_out')) {
                            $timeOut = \Carbon\Carbon::parse($get('time_out'));
                            $hoursToAdd = $state;

                            if ($hoursToAdd) {
                                // dd($hoursToAdd);
                                $newTimeIn = $timeOut->addHours((int)$hoursToAdd);
                                $set('time_in', $newTimeIn->format('H:i')); // فرمت زمان را تنظیم کنید
                            }
                        }
                    }),

                Forms\Components\DateTimePicker::make('date')->default(now())->required(),
                Forms\Components\Section::make([
                    Forms\Components\FileUpload::make('document')->downloadable(),
                    Forms\Components\Textarea::make('reason'),
                ])->columns(),

                // Forms\Components\Textarea::make('document')
                //     ->columnSpanFull(),
                // Forms\Components\Textarea::make('reason')
                //     ->columnSpanFull(),
                // Forms\Components\TextInput::make('status')
                //     ->required(),
                // Forms\Components\Textarea::make('comment')
                //     ->columnSpanFull(),
                // Forms\Components\DateTimePicker::make('approval_date'),
                // Forms\Components\Select::make('company_id')
                //     ->relationship('company', 'title')
                //     ->required(),
                // Forms\Components\Select::make('user_id')
                //     ->relationship('user', 'name'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('urgent_typeleave_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('time_out')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('time_in')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('hours')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('approval_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('company.title')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListUrgentLeaves::route('/'),
            'create' => Pages\CreateUrgentLeave::route('/create'),
            'edit' => Pages\EditUrgentLeave::route('/{record}/edit'),
        ];
    }
}
