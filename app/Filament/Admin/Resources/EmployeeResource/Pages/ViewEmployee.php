<?php

namespace App\Filament\Admin\Resources\EmployeeResource\Pages;

use App\Filament\Admin\Resources\EmployeeResource;
use App\Models\Employee;
use App\Models\Separation;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\IconSize;
use Illuminate\Contracts\Support\Htmlable;
use Spatie\Permission\Models\Role;

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
            \Filament\Actions\Action::make('Print')->label('Print ')->tooltip('Print Information')
                ->icon('heroicon-s-printer')
                ->iconSize(IconSize::Large)->url(fn($record) => route('pdf.employee', ['id' => $record]), true),
            Actions\EditAction::make()->color('success'),
            Actions\Action::make('separation')->label('Clearance')->form([
                DatePicker::make('date')->default(now())->label('Date of Resignation ')->required(),
                Textarea::make('reason')->columnSpanFull()->label('Reason for Resignation')->required(),
                Textarea::make('feedback')->columnSpanFull(),
            ])->action(function ($data) {
                $data['employee_id'] = $this->record->id;
                $data['company_id'] = getCompany()->id;
                $data['approved_by'] = auth()->user()->employee->id;
                $this->record->update(['leave_date' => $data['date']]);
                $roles = Role::query()->with('users')->whereHas('permissions', function ($query) {
                    return $query->where('name', 'clearanceApproval_employee');
                })->where('company_id', getCompany()->id)->get();
                $userIDs = [];
                foreach ($roles as $role) {
                    foreach ($role->users->pluck('id')->toArray() as $userID) {
                        $userIDs[] = $userID;
                    }
                }
                $employees = Employee::query()->whereIn('user_id', $userIDs)->get();
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
                        ImageEntry::make('pic')->state(function ($record) {
                            return $record->media->where('collection_name', 'images')->first()?->original_url;
                        })
                            ->defaultImageUrl(fn($record) => $record->gender === "male" ? asset('img/user.png') : asset('img/female.png'))
                            ->label('Employee Photo')
                            ->extraAttributes(['style' => 'border:1px solid rgba(var(--gray-200), var(--tw-border-opacity, 1));padding:3px;border-radius:5px'])
                            ->width(260)
                            ->height(260)
                            ->alignLeft()
                            ->columnSpan(2), // تغییر به 2 تا عکس در بالای امضا قرار بگیرد

                        ImageEntry::make('signature_pic')->state(function ($record) {
                            return $record->media->where('collection_name', 'signature')->first()?->original_url;
                        })
                            ->label('Employee Signature ')
                            ->extraAttributes(fn($record)=> $record->media->where('collection_name', 'signature')->first()?->original_url ? ['style' => 'border:1px solid rgba(var(--gray-200), var(--tw-border-opacity, 1));padding:3px;border-radius:5px'] :[] )
                            ->width(100)
                            ->height(100)
                            ->alignLeft()
                            ->columnSpan(1), // امضا در پایین با عرض 1

                    ])
                    ->columns(2) // تعداد ستون‌ها را 2 نگه دارید
                    ->columnSpan(2) // کل بخش را در دو ستون قرار دهید
                    ->extraAttributes([
                        'style' => 'display:flex; flex-direction: column; height: 100%; width: 100%; border-radius: 10px; justify-content: center; align-items: center;'
                    ]),



                section::make()
                    ->schema([
                        TextEntry::make('fullName')->extraAttributes(['style' => 'border:1px solid rgba(var(--gray-200), var(--tw-border-opacity, 1));padding:3px;border-radius:5px'])->label('Full Name')->state(fn($record) => $record->fullName)->size(TextEntry\TextEntrySize::Large),
                        textEntry::make('position.title')->extraAttributes(['style' => 'border:1px solid rgba(var(--gray-200), var(--tw-border-opacity, 1));padding:3px;border-radius:5px']),
                        TextEntry::make('email')->extraAttributes(['style' => 'border:1px solid rgba(var(--gray-200), var(--tw-border-opacity, 1));padding:3px;border-radius:5px'])->label('Email'),
                        TextEntry::make('phone_number')->extraAttributes(['style' => 'border:1px solid rgba(var(--gray-200), var(--tw-border-opacity, 1));padding:3px;border-radius:5px'])->label('Phone Number'),
                        TextEntry::make('address')->label('Address')->extraAttributes(['style' => 'border:1px solid rgba(var(--gray-200), var(--tw-border-opacity, 1));padding:3px;border-radius:5px']),
                    ])
                    ->columnSpan(1)->extraAttributes([
                        'style' => 'display:flex; height: 100%; border-radius: 10px; justify-content: center; align-items: center;'
                    ]),
                RepeatableEntry::make('emergency_contact')->label('Relatives Emergency Contact')
                    ->schema([
                        textEntry::make('name')->badge()->copyable()->inlineLabel(),
                        textEntry::make('relation')->badge()->copyable()->inlineLabel(),
                        textEntry::make('number')->badge()->copyable()->inlineLabel(),
                    ])->columnSpan(2)
                    ->columns(1)


            ])->columns(5)->columnSpanFull(),


            Section::make('Employee Profile')->schema([
                Split::make([
                    Section::make('Personal Information')->icon('heroicon-c-identification')->iconColor('success')->schema([
                        TextEntry::make('fullName')->label('Full Name')->extraAttributes(['style' => 'border:1px solid rgba(var(--gray-200), var(--tw-border-opacity, 1));padding:3px;border-radius:5px'])->copyable(),
                        textEntry::make('birthday')->extraAttributes(['style' => 'border:1px solid rgba(var(--gray-200), var(--tw-border-opacity, 1));padding:3px;border-radius:5px'])->date()->label('Date of Birth'),
                        textEntry::make('phone_number')->label('Phone Number')->extraAttributes(['style' => 'border:1px solid rgba(var(--gray-200), var(--tw-border-opacity, 1));padding:3px;border-radius:5px'])->copyable(),
                        textEntry::make('emergency_phone_number')->label('Emergency Contact Number')->extraAttributes(['style' => 'border:1px solid rgba(var(--gray-200), var(--tw-border-opacity, 1));padding:3px;border-radius:5px']),
                        textEntry::make('NIC')->copyable()->label('NIC')->extraAttributes(['style' => 'border:1px solid rgba(var(--gray-200), var(--tw-border-opacity, 1));padding:3px;border-radius:5px']),
                        textEntry::make('marriage')->state(function ($record) {
                            return match ($record->marriage) {
                                'divorced' => 'Divorced',
                                'widowed' => 'Widowed',
                                'married' => 'Married',
                                'single' => 'Single',
                                null => null
                            };
                        })->label('Marital Status')->extraAttributes(fn($state) => $state !== null ? ['style' => 'border:1px solid rgba(var(--gray-200), var(--tw-border-opacity, 1));padding:3px;border-radius:5px'] : []),
                        textEntry::make('count_of_child')->label('Number of Children')->extraAttributes(fn($state) => $state !== null ? ['style' => 'border:1px solid rgba(var(--gray-200), var(--tw-border-opacity, 1));padding:3px;border-radius:5px'] : []),
                        textEntry::make('gender')->state(function ($record) {
                            if ($record->gender === "male") {
                                return "Male";
                            } elseif ($record->gender === "female") {
                                return "Female";
                            } else {
                                return "Other";
                            }
                        })->extraAttributes(['style' => 'border:1px solid rgba(var(--gray-200), var(--tw-border-opacity, 1));padding:3px;border-radius:5px']),
                        textEntry::make('blood_group')->extraAttributes(['style' => 'border:1px solid rgba(var(--gray-200), var(--tw-border-opacity, 1));padding:3px;border-radius:5px']),
                        textEntry::make('city')->extraAttributes(['style' => 'border:1px solid rgba(var(--gray-200), var(--tw-border-opacity, 1));padding:3px;border-radius:5px']),
                        textEntry::make('address')->label('Employee Home Address')->extraAttributes(['style' => 'border:1px solid rgba(var(--gray-200), var(--tw-border-opacity, 1));padding:3px;border-radius:5px']),
                        textEntry::make('covid_vaccine_certificate')->label('Covid Vaccine Certificate')->state(fn($record) => $record->covid_vaccine_certificate ? "Yes" : "No")->extraAttributes(['style' => 'border:1px solid rgba(var(--gray-200), var(--tw-border-opacity, 1));padding:3px;border-radius:5px']),

                    ])->columns(2),
                    Section::make('Contract and Employment Status Details')->icon('cash')->iconColor('success')->schema([
                        textEntry::make('contract.title')->extraAttributes(['style' => 'border:1px solid rgba(var(--gray-200), var(--tw-border-opacity, 1));padding:3px;border-radius:5px']),
                        textEntry::make('department.title')->extraAttributes(['style' => 'border:1px solid rgba(var(--gray-200), var(--tw-border-opacity, 1));padding:3px;border-radius:5px']),
                        textEntry::make('duty.title')->extraAttributes(['style' => 'border:1px solid rgba(var(--gray-200), var(--tw-border-opacity, 1));padding:3px;border-radius:5px']),
                        textEntry::make('position.title')->extraAttributes(['style' => 'border:1px solid rgba(var(--gray-200), var(--tw-border-opacity, 1));padding:3px;border-radius:5px']),
                        textEntry::make('card_status')->extraAttributes(['style' => 'border:1px solid rgba(var(--gray-200), var(--tw-border-opacity, 1));padding:3px;border-radius:5px'])->label('Card Status'),
                        textEntry::make('type_of_ID')->extraAttributes(['style' => 'border:1px solid rgba(var(--gray-200), var(--tw-border-opacity, 1));padding:3px;border-radius:5px'])->label('Type Of ID'),
                        textEntry::make('ID_number')->extraAttributes(['style' => 'border:1px solid rgba(var(--gray-200), var(--tw-border-opacity, 1));padding:3px;border-radius:5px'])->label('ID Number'),
                        textEntry::make('structure')->extraAttributes(['style' => 'border:1px solid rgba(var(--gray-200), var(--tw-border-opacity, 1));padding:3px;border-radius:5px'])->state(fn($record) => $record?->warehouse?->title . " - " . $record?->structure?->title)->label('Duty Location '),
                        textEntry::make('joining_date')->extraAttributes(['style' => 'border:1px solid rgba(var(--gray-200), var(--tw-border-opacity, 1));padding:3px;border-radius:5px'])->label('Joining Date')->date(),
                        textEntry::make('leave_date')->extraAttributes(['style' => 'border:1px solid rgba(var(--gray-200), var(--tw-border-opacity, 1));padding:3px;border-radius:5px'])->label('Leave Date')->date(),
                    ])->columns(2),
                ])->from('md'),
                Split::make([
                    Section::make('Salary and Bank Information')->icon('cart')->iconColor('success')->schema([
                        textEntry::make('base_salary')->label('Base Salary')->numeric()->badge()->extraAttributes(['style' => 'border:1px solid rgba(var(--gray-200), var(--tw-border-opacity, 1));padding:3px;border-radius:5px']),
                        textEntry::make('daily_salary')->state(function ($record){

                            if ($record->daily_salary==null){
                                return number_format($record->base_salary/30,2);
                            }
                        })->label('Daily Salary')->numeric()->badge()->extraAttributes(['style' => 'border:1px solid rgba(var(--gray-200), var(--tw-border-opacity, 1));padding:3px;border-radius:5px']),
                        textEntry::make('branch')->extraAttributes(['style' => 'border:1px solid rgba(var(--gray-200), var(--tw-border-opacity, 1));padding:3px;border-radius:5px']),
                        textEntry::make('bank')->extraAttributes(['style' => 'border:1px solid rgba(var(--gray-200), var(--tw-border-opacity, 1));padding:3px;border-radius:5px']),
                        textEntry::make('benefits.title')->badge()->label('Allowances/Deductions')->extraAttributes(['style' => 'border:1px solid rgba(var(--gray-200), var(--tw-border-opacity, 1));padding:3px;border-radius:5px']),
                        textEntry::make('cart')->label('Bank Account')->extraAttributes(['style' => 'border:1px solid rgba(var(--gray-200), var(--tw-border-opacity, 1));padding:3px;border-radius:5px']),
                        textEntry::make('tin')->label('TIN')->extraAttributes(['style' => 'border:1px solid rgba(var(--gray-200), var(--tw-border-opacity, 1));padding:3px;border-radius:5px']),
                    ])->columns(4),

                ])->from('md'),

            ])
        ]);
    }
}
