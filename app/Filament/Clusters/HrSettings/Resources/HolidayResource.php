<?php

namespace App\Filament\Clusters\HrSettings\Resources;

use App\Filament\Clusters\HrSettings;
use App\Filament\Clusters\HrSettings\Resources\HolidayResource\Pages;
use App\Filament\Clusters\HrSettings\Resources\HolidayResource\RelationManagers;
use App\Models\Holiday;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

use function Laravel\Prompts\select;

class HolidayResource extends Resource
{
    protected static ?string $model = Holiday::class;

    protected static ?string $navigationIcon = 'heroicon-c-calendar-date-range';
    protected static ?string $label="Holiday";
    protected static ?string $pluralLabel="Holiday  ";
    protected static ?string $navigationGroup = 'HR Management System';

    protected static ?string $cluster = HrSettings::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->label('Details')->required()->maxLength(255),
                Forms\Components\DatePicker::make('date')->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->defaultSort('id', 'desc')->headerActions([
            ExportAction::make()
                ->after(function () {
                    if (Auth::check()) {
                        activity()
                            ->causedBy(Auth::user())
                            ->withProperties([
                                'action' => 'export',
                            ])
                            ->log('Export' . "Holiday");
                    }
                })->exports([
                    ExcelExport::make()->askForFilename("Holiday")->withColumns([
                       Column::make('name')->heading('Details'),
                       Column::make('date'),

                    ]),
                ])->label('Export Holiday')->color('purple')
        ])
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('name')->label('Details')->searchable(),
                Tables\Columns\TextColumn::make('date')->date()->sortable(),
            ])
            ->filters([
                Filter::make('date')
                ->form([
                    DatePicker::make('date_from'),
                    DatePicker::make('date_until'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['date_from'],
                            fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                        )
                        ->when(
                            $data['date_until'],
                            fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                        );
                }),
                Filter::make('Year/Month')
                ->form([
                    TextInput::make('Year'),
                    Select::make('Month')
                    ->options([
                        1 => 'January',
                        2 => 'February',
                        3 => 'March',
                        4 => 'April',
                        5 => 'May',
                        6 => 'June',
                        7 => 'July',
                        8 => 'August',
                        9 => 'September',
                        10 => 'October',
                        11 => 'November',
                        12 => 'December',
                    ])
                    ,
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['Year'],
                            fn (Builder $query, $year): Builder => $query->whereYear('date', $year),
                        )
                        ->when(
                            $data['Month'],
                            fn (Builder $query, $month): Builder => $query->whereMonth('date', $month),
                        );
                })
            ], getModelFilter())
            ->actions([
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\EditAction::make()->modelLabel('Edit'),
            ])
            ->bulkActions([
                ExportBulkAction::make()
                ->after(function () {
                    if (Auth::check()) {
                        activity()
                            ->causedBy(Auth::user())
                            ->withProperties([
                                'action' => 'export',
                            ])
                            ->log('Export' . "Holiday");
                    }
                })->exports([
                    ExcelExport::make()->askForFilename("Holiday")->withColumns([
                       Column::make('name')->heading('Details'),
                       Column::make('date'),

                    ]),
                ])->label('Export Holiday')->color('purple')
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
            'index' => Pages\ListHolidays::route('/'),
//            'create' => Pages\CreateHoliday::route('/create'),
//            'edit' => Pages\EditHoliday::route('/{record}/edit'),
        ];
    }
}
