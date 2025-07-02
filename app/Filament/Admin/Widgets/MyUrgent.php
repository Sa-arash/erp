<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Employee;
use App\Models\UrgentLeave;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class MyUrgent extends BaseWidget
{
    protected int | string | array $columnSpan='full';
    protected static ?string $heading='Urgent Leave';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                UrgentLeave::query()->where('employee_id',getEmployee()->id)
            )->headerActions([
                Tables\Actions\Action::make('new')->label('New Urgent Leave')->form([
                    Section::make([
                        DateTimePicker::make('date')->default(now())->required(),
                        TimePicker::make('time_out')->before(function (Get $get){
                            if ($get('time_in')){
                                return $get('time_in');
                            }
                            return false;
                        })->seconds(false)->reactive()
                            ->afterStateUpdated(function (Set $set, $state) {
                                $set('time_in', $state);
                            })->default(now())
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
                                        $newTimeIn = $timeOut->addHours((int)$hoursToAdd);
                                        $set('time_in', $newTimeIn->format('H:i'));
                                    }
                                }
                            }),
                    ])->columns(4),
                    Textarea::make('reason')->required()->columnSpanFull(),
                ])->action(function ($data){
                    $employee=getEmployee();
                    $urgent= UrgentLeave::query()->create(['employee_id'=>$employee->id,'company_id'=>$employee->company_id,'date'=>$data['date'],'hours'=>$data['hours'],'time_out'=>$data['time_out'],'time_in'=>$data['time_in'],'reason'=>$data['reason']]);
                    sendAR($employee,$urgent,getCompany());
                    Notification::make('new Urgent')->success()->title('New Urgent Leave Employee : '.$employee->fullName)->send()->sendToDatabase(auth()->user());
                })
            ])->filters([
                getFilterSubordinate()
            ])
            ->columns([
                Tables\Columns\TextColumn::make('NO')->label('NO')->rowIndex(),
                Tables\Columns\TextColumn::make('employee.fullName')->alignCenter()->searchable(),
                Tables\Columns\TextColumn::make('time_out')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('time_in')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('hours')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('date')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('approval_date')->dateTime()->sortable(),
                Tables\Columns\ImageColumn::make('approvals')->state(function ($record) {
                    $data = [];

                    $data[]=$record->employee->media->where('collection_name', 'images')->first()?->original_url;

                    foreach ($record->approvals as $approval) {
                        if ($approval->status->value == "Approve") {
                            if ($approval->employee->media->where('collection_name', 'images')->first()?->original_url) {
                                $data[] = $approval->employee->media->where('collection_name', 'images')->first()?->original_url;
                            } else {
                                $data[] = $approval->employee->gender === "male" ? asset('img/user.png') : asset('img/female.png');
                            }
                        }
                    }
                    if ($record->admin){
                        $data[]=$record->admin->media->where('collection_name', 'images')->first()?->original_url;
                    }
                    return $data;
                })->circular()->stacked(),
            ]);
    }
}
