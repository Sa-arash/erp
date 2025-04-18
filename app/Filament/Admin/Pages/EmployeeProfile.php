<?php

namespace App\Filament\Admin\Pages;

use App\Models\Employee;
use App\Models\PurchaseOrder;
use App\Models\Separation;
use Filament\Actions\Action;
use Filament\Forms\Components\Actions\Action as ComponentsActionsAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section as ComponentsSection;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Set;
use Filament\Infolists\Components\Actions\Action as ActionsAction;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\IconSize;
use Spatie\Permission\Models\Role;

use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use TomatoPHP\FilamentMediaManager\Form\MediaManagerInput;

class EmployeeProfile extends Page implements HasForms, HasInfolists
{
    use InteractsWithInfolists;
    use InteractsWithForms;
    protected static ?string $navigationIcon = 'heroicon-c-user-circle';

    protected static string $view = 'filament.admin.pages.employee-profile';



    protected static ?string $navigationLabel = 'View Profile';


    protected function getHeaderActions(): array
    {
        return [
            Action::make('change Information')->record(getEmployee())
                ->form(
                    function () {
                        $record = auth()->user();
                        return [
                            ComponentsSection::make('Employee info')->schema([
                                MediaManagerInput::make('images')->extraAttributes(['style' => 'width:150px!important;border-radius:10px !important'])->orderable(false)->folderTitleFieldName("fullName")->image(true)->disk('public')->maxItems(1)->schema([]),
                                MediaManagerInput::make('signature')->extraAttributes(['style' => 'width:150px!important;border-radius:10px !important'])->orderable(false)->image(true)->folderTitleFieldName("fullName")->disk('public')->maxItems(1)->schema([]),
                                TextInput::make('email')->default($record->email)->email()->rule(Rule::unique('users', 'email')->whereNot('email', $record->email))->required()->maxLength(255),
                                TextInput::make('password')->hintAction(ComponentsActionsAction::make('generate_password')->action(function (Set $set) {
                                    $password = Str::password(8);
                                    $set('password', $password);
                                    $set('password_confirmation', $password);
                                }))->dehydrated(fn(?string $state): bool => filled($state))->revealable()->required(fn(string $operation): bool => $operation === 'create')->configure()->same('password_confirmation')->password(),
                                TextInput::make('password_confirmation')->revealable()->required(fn(string $operation): bool => $operation === 'create')->password()
                            ])->columns(2)
                        ];
                    }
                )

                ->action(function ($data, $record) {
                    $record = auth()->user();
                    $record->update([
                        'email' => $data['email'],
                        'password' => $data['password']??auth()->user()->password,
                    ]);
                    $record->employee->update([
                        'email' => $data['email']??$record->employee->email,
                    ]);
                    Notification::make('successfull')->success()->title('Successfully')->send()->sendToDatabase(auth()->user());
                }),
            Action::make('separation')->label('Clearance')->form([
                DatePicker::make('date')->default(now())->label('Date of Resignation ')->required(),
                Textarea::make('reason')->columnSpanFull()->label('Reason for Resignation')->required(),
                Textarea::make('feedback')->columnSpanFull(),
            ])->action(function ($data) {

                $data['employee_id'] = auth()->user()->employee->id;
                $data['company_id'] = getCompany()->id;
                $data['approved_by'] = auth()->user()->employee->id;
                $this->record->update(['leave_date' => $data['date']]);
                $roles = Role::query()->with('users')->whereHas('permissions', function ($query) {
                    return $query->where('name', 'clearance_employee');
                })->where('company_id', getCompany()->id)->get();
                $userIDs = [];
                foreach ($roles as $role) {
                    foreach ($role->users->pluck('id')->toArray() as $userID) {
                        $userIDs[] = $userID;
                    }
                }
                $employees = Employee::query()->whereIn('user_id', $userIDs)->get();
                $record = Separation::query()->create($data);
                foreach ($employees as $employee) {
                    $record->approvals()->create([
                        'employee_id' => $employee->id,
                        'company_id' => getCompany()->id,
                        'position' => $employee->position?->title,
                        'status' => "Pending"
                    ]);
                }
                Notification::make('success')->title('Clearance Submitted Successfully')->success()->send()->sendToDatabase(auth()->user());
            })->color('danger')->hidden(fn($record) => isset(auth()->user()->employee->separation)),
            Action::make('View Clearance')->visible(fn($record) => isset(auth()->user()->employee->separation))->label('View Clearance')->record(auth()->user()->employee)->infolist(function () {
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
        return $infolist
            ->record(auth()->user()->employee)

            ->schema([
                Section::make('Employee Overview')->schema([
                    section::make()
                        ->schema([
                            ImageEntry::make('pic')
                                ->defaultImageUrl(fn($record) => $record->gender === "male" ?  asset('img/user.png') : asset('img/female.png'))
                                ->label('')->state(function ($record){
                                    return $record->media->where('collection_name','images')->first()?->original_url;
                                })->extraAttributes(['style' => 'border-radius: 10px;  padding: 0px;margin:0px;'])->width(200)

                                ->height(200)
                                ->alignLeft()
                                ->columnSpan(1),
                            ImageEntry::make('signature_pic')
                                ->label('Employee Signature ')
                                ->extraAttributes(['style' => 'border-radius: 10px;  padding: 0px;margin:0px;'])
                                ->width(100)
                                ->state(function ($record){
                                    return $record->media->where('collection_name','signature')->first()?->original_url;
                                })
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
                                ->state(fn($record) => $record->fullName . "(" . $record?->user?->roles->pluck('name')->join(', ') . ")")
                                ->size(TextEntry\TextEntrySize::Large)
                                ->inlineLabel(),
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
                        Section::make('Information')->icon('heroicon-c-identification')->iconColor('success')->schema([
                            TextEntry::make('fullName')->hintAction(
                                \Filament\Infolists\Components\Actions\Action::make('pdf')
                                    ->tooltip('Print Information')
                                    ->icon('heroicon-s-printer')
                                    ->iconSize(IconSize::Large)
                                    ->label('')
                                    ->url(fn($record) => route('pdf.employee', ['id' => $record->id]))
                                    ->openUrlInNewTab()->label('Print')
                            )->copyable(),                            textEntry::make('birthday')->date(),
                            textEntry::make('phone_number')->copyable(),
                            textEntry::make('emergency_phone_number'),
                            textEntry::make('NIC')->copyable()->label('NIC'),
                            textEntry::make('marriage'),
                            textEntry::make('count_of_child'),
                            textEntry::make('gender')->state(function ($record) {
                                if ($record->gender === "male") {
                                    return "Male";
                                } elseif ($record->gender === "female") {
                                    return "Female";
                                } else {
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
                            textEntry::make('structure')->state(fn($record) => $record?->warehouse?->title . " - " . $record?->structure?->title)->label('Duty Location(Building And Room) '),
                            textEntry::make('joining_date')->label('Joining Date')->date(),
                            textEntry::make('leave_date')->date()->label('Leave Date'),
                        ])->columns(2),

                    ])->from('md'),
                    Split::make([
                        Section::make('Salary and Bank Information')->icon('cart')->iconColor('success')->schema([
                            textEntry::make('base_salary')->numeric()->badge(),
                            textEntry::make('benefits.title')->badge()->label('Allowances/Deductions'),
                            textEntry::make('cart')->label('Bank Account'),
                            textEntry::make('bank'),
                            textEntry::make('branch'),
                            textEntry::make('tin')->label('TIN'),
                        ])->columns(2),

                    ])->from('md'),
                    RepeatableEntry::make('Relatives Emergency Contact')
                        ->schema([
                            textEntry::make('name')->badge()->copyable()->inlineLabel(),
                            textEntry::make('relation')->badge()->copyable()->inlineLabel(),
                            TextEntry::make('number')->badge()->copyable()->inlineLabel(),
                        ])
                        ->columns(3)
                ])
            ])
        ;
    }
}
