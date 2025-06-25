<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Employee;
use App\Models\Overtime;
use App\Models\UrgentLeave;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Support\Enums\IconSize;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class myEmployees extends BaseWidget
{
    protected int | string | array $columnSpan='full';
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Employee::query()->where('manager_id',getEmployee()->id)
            )
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\ImageColumn::make('media.original_url')->state(function ($record) {
                       return $record->media->where('collection_name','images')->first()?->original_url;
                })->disk('public')->defaultImageUrl(fn($record) => $record->gender === "male" ? asset('img/user.png') : asset('img/female.png'))->alignLeft()->label('Profile Picture')->width(50)->height(50)->extraAttributes(['style' => 'border-radius:50px!important']),
                Tables\Columns\TextColumn::make('fullName')->sortable()->alignLeft()->searchable(),
                Tables\Columns\TextColumn::make('gender')->state(function ($record) {
                    if ($record->gender === "male") {
                        return "Male";
                    } elseif ($record->gender === "female") {
                        return "Female";
                    } else {
                        return "Other";
                    }
                })->alignLeft()->sortable(),
                Tables\Columns\TextColumn::make('phone_number')->alignLeft()->sortable()->searchable(),
                Tables\Columns\TextColumn::make('duty.title')->alignLeft()->numeric()->sortable()->searchable(),
                Tables\Columns\TextColumn::make('base_salary')->label('Base Salary' . "(" . defaultCurrency()?->symbol . ")")->alignLeft()->numeric()->sortable()->badge(),
                Tables\Columns\TextColumn::make('department.title')->alignLeft()->color('aColor')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('position.title')->alignLeft()->label('Position')->sortable(),
                Tables\Columns\TextColumn::make('manager.fullName')->alignLeft()->label('Manager')->sortable(),
            ])->actions([
                Tables\Actions\Action::make('overTime')->label('Overtime')->form([
                    TextInput::make('title')->label('Description')->required()->maxLength(255),
                    DatePicker::make('overtime_date')->default(now())->label('Overtime Date')->required(),
                    TextInput::make('hours')->numeric()->required()
                ])->action(function ($record,$data){

                    Overtime::query()->create(['title'=>$data['title'], 'employee_id'=>$record->id, 'company_id'=>$record->company_id, 'user_id'=>auth()->id(), 'overtime_date'=>$data['overtime_date'],'hours'=>$data['hours']]);
                    Notification::make('success')->success()->title('Create Overtime for Employee :'.$record->fullName)->send()->sendToDatabase(auth()->user());
                }),
                Tables\Actions\Action::make('urgent')->fillForm(function ($record){
                    return [
                        'date'=>now(),
                        'employee_id'=>$record->id,
                        'number'=>$record->ID_number,
                        'time_out'=>now()
                    ];
                })->label('Urgent Leave')->color('warning')->icon('heroicon-s-arrow-left-on-rectangle')->form([
                    Section::make([
                        DateTimePicker::make('date')->default(now())->required(),
                        Select::make('employee_id')->afterStateUpdated(function ($state,Set $set){
                            $employee=  Employee::query()->firstWhere('id',$state);
                            if ($employee){
                                $set('number',$employee->ID_number);
                            }
                        })->label('Employee')->required()->live()->options(Employee::query()->where('manager_id',getEmployee()->id)->pluck('fullName', 'id'))->searchable()->preload(),
                        TextInput::make('number')->disabled()->label('Badge Number'),
                    ])->columns(3),
                    Section::make([
                        TimePicker::make('time_out')->before(function (Get $get){
                            if ($get('time_in')){
                                return $get('time_in');
                            }
                            return false;
                        })->seconds(false)->reactive()
                            ->afterStateUpdated(function (Set $set, $state) {
                                $set('time_in', $state);
                            })
                            ->required(),
                        TimePicker::make('time_in')
                            ->after('time_out')
                            ->seconds(false),

                        TextInput::make('hours')->numeric()
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
                    ])->columns(3),
                     Textarea::make('reason')->columnSpanFull(),
                ])->action(function ($data){
                    $company=getCompany();
                    $urgent= UrgentLeave::query()->create(['employee_id'=>$data['employee_id'],'company_id'=>$company->id,'date'=>$data['date'],'hours'=>$data['hours'],'time_out'=>$data['time_out'],'time_in'=>$data['time_in'],'reason'=>$data['reason'],'status'=>'approveHead']);
                    $urgent->approvals()->create([
                        'employee_id' => getEmployee()->id,
                        'company_id' => $company->id,
                        'position' => 'Head',
                        'status'=>'Approve',
                        'approve_date'=>now()
                    ]);
                    sendSuccessNotification();
                })
            ]);
    }
}
