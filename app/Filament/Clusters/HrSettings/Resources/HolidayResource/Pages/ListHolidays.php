<?php

namespace App\Filament\Clusters\HrSettings\Resources\HolidayResource\Pages;

use App\Filament\Clusters\HrSettings\Resources\HolidayResource;
use Closure;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;

class ListHolidays extends ListRecords
{
    protected static string $resource = HolidayResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('Extra Setting')->form([
                select::make('weekend_days')
                    ->label('Weekend Days')
                    ->multiple()
                    ->options([
                        'saturday' => 'Saturday',
                        'sunday' => 'Sunday',
                        'monday' => 'Monday',
                        'tuesday' => 'Tuesday',
                        'wednesday' => 'Wednesday',
                        'thursday' => 'Thursday',
                        'friday' => 'Friday',
                    ])->default(getCompany()->weekend_days)
                    ->placeholder('Select weekend days')
                    ->required(),
                TextInput::make('daily_working_hours')
                    ->label('Daily Working Hours')
                    ->numeric()
                    ->required()->default(getCompany()->daily_working_hours)
                    ->rules([
                        fn (): Closure => function (string $attribute, $value, Closure $fail) {
                            if ($value <= 0) {
                                $fail('The :attribute must be greater    than 0.');
                            }
                        },
                    ]),
                TextInput::make('overtime_rate')
                    ->label('Overtime Rate(Overtime Pay Rate Based on Hourly Wage)')
                    ->numeric()
                    ->default(1.5)
                    ->required()->default(getCompany()->overtime_rate),
            ])->action(function ($data){

                getCompany()->update($data);
            })
        ];
    }
}
