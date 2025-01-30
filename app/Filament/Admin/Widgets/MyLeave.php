<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Employee;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\Typeleave;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class MyLeave extends BaseWidget
{
    protected int | string | array $columnSpan='full';
    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                Tables\Actions\Action::make('New Leave')->action(function ($data){
                    $data['company_id']=getCompany()->id;
                    $data['employee_id']=getEmployee()->id;
                    Leave::query()->create($data);
                })->label('New Leave')->form([
                   Section::make([
                           Select::make('typeleave_id')->columnSpanFull()->label('Leave Type')->required()->options(Typeleave::query()->where('company_id', getCompany()->id)->pluck('title', 'id'))->searchable()->preload(),
                           DatePicker::make('start_leave')->default(now())->live()->afterStateUpdated(function ( Get $get ,Set $set){
                               $start = Carbon::parse($get('start_leave'));
                               $end = Carbon::parse($get('end_leave'));
                               $period = CarbonPeriod::create($start, $end);
                               $daysBetween = $period->count(); // تعداد کل روزها
                               $CompanyHoliday = count(getDaysBetweenDates($start, $end, getCompany()->weekend_days));

                               $holidays = Holiday::query()->where('company_id', getCompany()->id)->whereBetween('date', [$start, $end])->count();
                               $validDays = $daysBetween - $holidays-$CompanyHoliday;
                               $set('days', $validDays);
                           })->required()->default(now()),
                           DatePicker::make('end_leave')->default(now())->afterStateUpdated(function ( Get $get ,Set $set){
                               $start = Carbon::parse($get('start_leave'));
                               $end = Carbon::parse($get('end_leave'));
                               $period = CarbonPeriod::create($start, $end);
                               $daysBetween = $period->count(); // تعداد کل روزها
                               $CompanyHoliday = count(getDaysBetweenDates($start, $end, getCompany()->weekend_days));

                               $holidays = Holiday::query()->where('company_id', getCompany()->id)->whereBetween('date', [$start, $end])->count();
                               $validDays = $daysBetween - $holidays-$CompanyHoliday;
                               $set('days', $validDays);
                           })->live()->required(),
                           TextInput::make('days')->columnSpanFull()->required()->numeric(),
                           Section::make([
                               FileUpload::make('document')->downloadable(),
                               Textarea::make('description'),
                           ])->columns()
                       ])->columns()
            ])])
            ->query(
                Leave::query()->where('employee_id',auth()->user()->id)
            )
            ->columns([
                Tables\Columns\TextColumn::make('')->alignCenter()->rowIndex(),
                Tables\Columns\TextColumn::make('typeLeave.title')->alignCenter()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Request Date')->date()->alignCenter()->sortable(),
                Tables\Columns\TextColumn::make('approval_date')->alignCenter()->tooltip(fn($record) => $record->user?->name)->date()->sortable(),
                Tables\Columns\TextColumn::make('start_leave')->date()->sortable(),
                Tables\Columns\TextColumn::make('end_leave')->date()->sortable(),
                Tables\Columns\TextColumn::make('days')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('status')->badge(),
            ]);
    }
}
