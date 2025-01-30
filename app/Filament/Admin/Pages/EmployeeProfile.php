<?php

namespace App\Filament\Admin\Pages;

use App\Models\Employee;
use App\Models\PurchaseOrder;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section as ComponentsSection;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Components\Actions\Action as ActionsAction;
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
            Action::make('change image')
            ->form(
                function(){
                  $record = auth()->user()->employee;
                    return[
                        ComponentsSection::make('Employee info')->schema([
                            FileUpload::make('pic')
                            ->default($record?->pic)
                            
                            ->label('Profile Picture')->image()->columnSpan(1)->imageEditor()->extraAttributes(['style' => 'width:150px!important;border-radius:10px !important']),
                            FileUpload::make('signature_pic')
                            ->default($record?->signature_pic)
                            ->label('Signature')->image()->columnSpan(1)->imageEditor()->extraAttributes(['style' => 'width:150px!important;border-radius:10px !important']),
                        ])->columns(2)
                        ];
                })
                

                ->action(function ($data, $record) {
                    $record = auth()->user()->employee;
                    $record->update([
                        'pic'=>$data['pic'],
                        'signature_pic'=>$data['signature_pic'],
                    ]);
                    Notification::make('successfull')->success()->title('Success Full')->send()->sendToDatabase(auth()->user());
                }),


          
           
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
                            ->defaultImageUrl(fn($record)=>$record->gender==="male" ?  asset('img/user.png') :asset('img/female.png'))
                            ->label('')
                            ->extraAttributes(['style' => 'border-radius: 10px;  padding: 0px;margin:0px;'])
                            ->width(200)

                            ->height(200)
                            ->alignLeft()
                            ->columnSpan(1),
                        ImageEntry::make('signature_pic')
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
                            ->state(fn($record) => $record->fullName . "(" . $record->user->roles->pluck('name')->join(', ') . ")")
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
                        TextEntry::make('fullName')->copyable(),
                        textEntry::make('birthday')->date(),
                        textEntry::make('phone_number')->copyable(),
                        textEntry::make('emergency_phone_number'),
                        textEntry::make('NIC')->copyable()->label('NIC'),
                        textEntry::make('marriage'),
                        textEntry::make('count_of_child'),
                        textEntry::make('gender'),
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
                        textEntry::make('joining_date')->label('Joining Date')->date(),
                        textEntry::make('leave_date'),





                        // TextEntry::make('phone_number')->copyable()->color('aColor')->url(fn($record) => 'tel:' . $record->phone_number)->label('شماره موبایل')->inlineLabel(),
                        // TextEntry::make('tel')->label('شماره تلفن')->color('aColor')->url(fn($record) => 'tel:' . $record->phone_number)->inlineLabel()->copyable(),
                        // TextEntry::make('father_number')->copyable()->label('شماره موبایل پدر')->color('aColor')->url(fn($record) => 'tel:' . $record->fhather_number)->inlineLabel(),
                        // TextEntry::make('mather_number')->copyable()->label('  شماره موبایل مادر')->color('aColor')->url(fn($record) => 'tel:' . $record->mather_number)->inlineLabel(),
                        // TextEntry::make('eitaa_number')->copyable()->label('ایتا')->inlineLabel(),
                        // TextEntry::make('telegram_number')->copyable()->label('تلگرام')->inlineLabel(),
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
