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
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class MyLeave extends BaseWidget
{

    protected int | string | array $columnSpan='full';
    public function table(Table $table): Table
    {
        return $table ->query(
            Leave::query()->where('employee_id',getEmployee()->id)
        )->defaultSort('id','desc')
            ->headerActions([
                Tables\Actions\Action::make('New Leave')->action(function ($data){
                    $data['company_id']=getCompany()->id;
                    $data['employee_id']=getEmployee()->id;

                    $leave= Leave::query()->create($data);
                    $employee=getEmployee();
                    if ($employee->manager_id){
                        $leave->approvals()->create([
                            'position'=>'Manager',
                            'employee_id'=>$employee->manager_id,
                            'company_id'=>getCompany()->id
                        ]);
                    }
                    Notification::make('success')->title('Created')->send();
                })->label('New Leave')->form([
                   Section::make([
                       ToggleButtons::make('type')->required()->grouped()->boolean('R&R','Home'),
                           Select::make('typeleave_id')->label('Leave Type')->required()->options(Typeleave::query()->where('company_id', getCompany()->id)->pluck('title', 'id'))->searchable()->preload(),
                           DatePicker::make('start_leave')->default(now())->live()->afterStateUpdated(function ( Get $get ,Set $set){
                               $start = Carbon::parse($get('start_leave'));
                               $end = Carbon::parse($get('end_leave'));
                               $period = CarbonPeriod::create($start, $end);
                               $daysBetween = $period->count(); // تعداد کل روزها
                               $CompanyHoliday = count(getDaysBetweenDates($start, $end, getCompany()->weekend_days));

                               $holidays = Holiday::query()->where('company_id', getCompany()->id)->whereBetween('date', [$start, $end])->count();
                               $validDays = $daysBetween - $holidays-$CompanyHoliday;
                               $set('days', $validDays);
                           })->required()->default(now())->live(),
                           DatePicker::make('end_leave')->default(now())->afterStateUpdated(function ( Get $get ,Set $set){
                               $start = Carbon::parse($get('start_leave'));
                               $end = Carbon::parse($get('end_leave'));
                               $period = CarbonPeriod::create($start, $end);
                               $daysBetween = $period->count(); // تعداد کل روزها
                               $CompanyHoliday = count(getDaysBetweenDates($start, $end, getCompany()->weekend_days));

                               $holidays = Holiday::query()->where('company_id', getCompany()->id)->whereBetween('date', [$start, $end])->count();
                               $validDays = $daysBetween - $holidays-$CompanyHoliday;
                               $set('days', $validDays);
                           })->live()->required()->afterOrEqual(fn(Get $get)=>$get('start_leave')),
                           TextInput::make('days')->columnSpanFull()->required()->numeric(),
                           ToggleButtons::make('is_circumstances')->live()->default(0)->required()->boolean('Yes','No')->grouped()->label('Aware of any Circumstances'),
                           Textarea::make('explain_leave')->required(fn(Get $get)=>$get('is_circumstances'))->label('Explain'),
                           Section::make([
                               FileUpload::make('document')->downloadable(),
                               Textarea::make('description'),
                           ])->columns()
                       ])->columns()
            ])])

            ->columns([
                Tables\Columns\TextColumn::make('#')->alignCenter()->rowIndex(),
                Tables\Columns\TextColumn::make('typeLeave.title')->alignCenter()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Request Date')->dateTime()->alignCenter()->sortable(),
                Tables\Columns\TextColumn::make('approvals.employee.fullName')->label('Line Manager')->alignCenter()->sortable(),
                Tables\Columns\TextColumn::make('approval_date')->alignCenter()->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('start_leave')->date()->sortable(),
                Tables\Columns\TextColumn::make('end_leave')->date()->sortable(),
                Tables\Columns\TextColumn::make('days')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('status')->badge(),
            ])->actions([
                Tables\Actions\Action::make('pdf')->tooltip('Print')->icon('heroicon-s-printer')->label('')
                ->url(fn($record) => route('pdf.leaverequest', ['id' => $record->id]))->openUrlInNewTab(),
                Tables\Actions\DeleteAction::make()->action(function ($record){
                    $record->approvals()->delete();
                    $record->delete();
                    Notification::make('success')->success()->title('Successfully')->send();
                })->visible(fn($record)=>$record->status->value=='pending')
            ]);
    }
}
