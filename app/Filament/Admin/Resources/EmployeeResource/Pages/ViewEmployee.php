<?php

namespace App\Filament\Admin\Resources\EmployeeResource\Pages;

use App\Filament\Admin\Resources\EmployeeResource;
use App\Models\Employee;
use App\Models\Permission;
use Filament\Forms\Components\Textarea;
use Spatie\Permission\Models\Role;
use App\Models\Separation;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class  ViewEmployee extends ViewRecord
{
    protected static string $resource = EmployeeResource::class;
    public function getTitle(): string|Htmlable
    {
        return $this->record->fullName;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()->color('success'),
            Actions\Action::make('separation')->label('Clearance')->form([
                DatePicker::make('date')->default(now())->label('Date of Resignation ')->required(),
                Textarea::make('reason')->columnSpanFull()->label('Reason for Resignation')->required(),
                Textarea::make('feedback')->columnSpanFull(),
            ])->action(function ($data){
                $data['employee_id']=$this->record->id;
                $data['company_id']=getCompany()->id;
                $data['approved_by']=auth()->user()->employee->id;
                $this->record->update(['leave_date'=> $data['date']]);
                $roles=Role::query()->with('users')->whereHas('permissions',function ($query){
                   return $query->where('name','clearanceApproval_employee');
                })->where('company_id',getCompany()->id)->get();
                $userIDs=[];
                foreach ($roles as $role){
                    foreach ($role->users->pluck('id')->toArray() as $userID ){
                        $userIDs[]=$userID ;
                    }
                }
                $employees= Employee::query()->whereIn('user_id',$userIDs)->get();
                $record=Separation::query()->create($data);
                foreach ($employees as $employee){
                    $record->approvals()->create([
                        'employee_id' => $employee->id,
                        'company_id' => getCompany()->id,
                        'position' => $employee->position?->title,
                        'status' => "Pending"
                    ]);
                }
                Notification::make('success')->title('Clearance Submitted Successfully')->success()->send()->sendToDatabase(auth()->user());
            })->color('danger')->hidden(fn($record)=>isset($record->separation)),
            Actions\Action::make('View Clearance')->visible(fn($record)=>isset($record->separation))->label('View Clearance')->infolist(function (){
                return [
                    Section::make([
                        TextEntry::make('fullName'),
                        TextEntry::make('date'),
                        Fieldset::make('Clearance')->relationship('separation')->schema([
                            RepeatableEntry::make('approvals')->schema([
                                TextEntry::make('employee.info'),
                                TextEntry::make('position'),
                                TextEntry::make('status'),
                                TextEntry::make('approve_date')->date(),
                                TextEntry::make('comment')
                            ])->columnSpanFull()->columns(4)
                        ])
                    ])->columns()
                ];
            })
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Employee Overview')->schema([
                section::make()
                    ->schema([
                        ImageEntry::make('pic')->state(function ($record){
                            return $record->media->where('collection_name','images')->first()?->original_url;
                        })
                            ->defaultImageUrl(fn($record)=>$record->gender==="male" ?  asset('img/user.png') :asset('img/female.png'))
                            ->label('')
                            ->extraAttributes(['style' => 'border-radius: 10px;  padding: 0px;margin:0px;'])
                            ->width(200)

                            ->height(200)
                            ->alignLeft()
                            ->columnSpan(1),
                        ImageEntry::make('signature_pic')->state(function ($record){
                            return $record->media->where('collection_name','signature')->first()?->original_url;

                        })
                            ->label('Employee Signature ')
                            ->extraAttributes(['style' => 'border-radius: 10px;  padding: 0px;margin:0px;'])
                            ->width(100)

                            ->height(100)
                            ->alignLeft()
                            ->columnSpan(1),


                    ])
                    ->columns(2)->columnSpan(2)->extraAttributes([
                        'style' => 'display:flex; height: 100%; width: 100%; border-radius: 10px; justify-content: center; align-items: center;'
                    ]),


                section::make()
                    ->schema([
                        TextEntry::make('fullName')
                            ->label('Full Name')
                            ->state(fn($record) => $record->fullName)
                            ->size(TextEntry\TextEntrySize::Large)
                            ,
                        textEntry::make('position.title'),

                        TextEntry::make('email')
                            ->label('Email'),
                        TextEntry::make('address')
                            ->label('Address'),

                    ])
                    ->columnSpan(3)->extraAttributes([
                        'style' => 'display:flex; height: 100%; border-radius: 10px; justify-content: center; align-items: center;'
                    ]),



            ])->columns(5)->columnSpanFull(),





            Section::make('Profile')->schema([
                Split::make([
                    Section::make('Personal Information')->icon('heroicon-c-identification')->iconColor('success')->schema([
                        TextEntry::make('fullName')->copyable(),
                        textEntry::make('birthday')->date(),
                        textEntry::make('phone_number')->copyable(),
                        textEntry::make('emergency_phone_number'),
                        textEntry::make('NIC')->copyable()->label('NIC'),
                        textEntry::make('marriage'),
                        textEntry::make('count_of_child'),
                        textEntry::make('gender')->state(function($record){
                            if ($record->gender==="male"){
                                return "Male";
                            }elseif ($record->gender==="female"){
                                return "Female";
                            }else{
                                return  "Other";
                            }
                        }),
                        textEntry::make('blood_group'),
                        textEntry::make('city'),
                        textEntry::make('address'),
                        textEntry::make('covid_vaccine_certificate')->state(fn($record) => $record->covid_vaccine_certificate ?  "Yes" : "No"),

                    ])->columns(2),
                    Section::make('Contract and Employment Status Details')->icon('cash')->iconColor('success')->schema([
                        textEntry::make('contract.title'),
                        textEntry::make('department.title'),
                        textEntry::make('duty.title'),
                        textEntry::make('position.title'),
                        textEntry::make('card_status')->label('Card Status'),
                        textEntry::make('type_of_ID')->label('Type Of ID'),
                        textEntry::make('ID_number')->label('ID Number'),
                        textEntry::make('structure')->state(fn($record)=>$record?->warehouse?->title." - ".$record?->structure?->title)->label('Duty Location(Building And Room) '),
                        textEntry::make('joining_date')->label('Joining Date')->date(),
                        textEntry::make('leave_date')->label('Leave Date')->date(),
                    ])->columns(2),
                ])->from('md'),
                Split::make([
                    Section::make('Salary and Bank Information')->icon('cart')->iconColor('success')->schema([
                        textEntry::make('base_salary')->numeric()->badge(),
                        textEntry::make('daily_salary')->numeric()->badge(),
                        textEntry::make('branch'),
                        textEntry::make('bank'),
                        textEntry::make('benefits.title')->badge()->label('Allowances/Deductions'),
                        textEntry::make('cart')->label('Bank Account'),
                        textEntry::make('tin')->label('TIN'),
                    ])->columns(4),

                ])->from('md'),
                RepeatableEntry::make('Relatives Emergency Contact')
                    ->schema([
                        textEntry::make('name')->badge()->copyable()->inlineLabel(),
                        textEntry::make('relation')->badge()->copyable()->inlineLabel(),
                        textEntry::make('number')->badge()->copyable()->inlineLabel(),
                    ])
                    ->columns(3)
            ])
        ]);
    }
}
