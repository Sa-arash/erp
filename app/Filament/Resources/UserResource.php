<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\Benefit;
use App\Models\Company;
use App\Models\Contract;
use App\Models\Department;
use App\Models\Duty;
use App\Models\Position;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required()->maxLength(255),
                Forms\Components\TextInput::make('email')->live(true)->afterStateUpdated(function (Forms\Set $set,Get $get){
                    $set('employee.email',$get('email'));
                })->email()->unique('users', 'email', ignoreRecord: true)->required()->maxLength(255),
                Forms\Components\TextInput::make('password')->hintAction(Forms\Components\Actions\Action::make('generate_password')->action(function (Forms\Set $set) {
                    $password = Str::password(8);
                    $set('password', $password);
                    $set('password_confirmation', $password);
                }))->dehydrated(fn(?string $state): bool => filled($state))->revealable()->required(fn(string $operation): bool => $operation === 'create')->configure()->same('password_confirmation')->password(),
                Forms\Components\TextInput::make('password_confirmation')->revealable()->required(fn(string $operation): bool => $operation === 'create')->password(),
                Forms\Components\Checkbox::make('haveEmployee')->label('Create Employee Profile ')->live(),
            Forms\Components\Fieldset::make('Employee')->visible(fn(Get $get)=>$get('haveEmployee'))->relationship('employee')->schema([
                    Forms\Components\Wizard::make([
                        Forms\Components\Wizard\Step::make('Information')
                            ->schema([
                                Forms\Components\FileUpload::make('pic')->label('Profile Picture')->image()->columnSpanFull()->imageEditor()->extraAttributes(['style' => 'width:150px!important;border-radius:10px !important']),
                                Forms\Components\Select::make('company_id')->required()->label('Company')->columnSpanFull()->searchable()->preload()->live()->options(Company::query()->pluck('title','id')),
                                Forms\Components\TextInput::make('fullName')->required()->maxLength(255),
                                Forms\Components\TextInput::make('email')->readOnly()->email()->required()->maxLength(255),
                                Forms\Components\DatePicker::make('birthday')->label('Date Birthday'),
                                Forms\Components\TextInput::make('phone_number')->tel()->required()->maxLength(255),
                                Forms\Components\TextInput::make('emergency_phone_number')->tel()->maxLength(255),
                                Forms\Components\TextInput::make('NIC')->label('NIC')->maxLength(255),
                                Forms\Components\Select::make('marriage')->label('Marital Status')->options(['divorced' => 'Divorced', 'widowed' => 'Widowed', 'married' => 'Married', 'single' => 'Single',])->searchable()->preload(),
                                Forms\Components\TextInput::make('count_of_child')->label('Number Of Child')
                                    ->numeric()
                                    ->default(0),
                                Forms\Components\Select::make('blood_group')
                                    ->options([
                                        "O negative",
                                        "O positive",
                                        "A negative",
                                        "A positive",
                                        "B negative",
                                        "B positive",
                                        "B positive",
                                        "AB negative",
                                        "AB positive",
                                    ])->searchable(),
                                Forms\Components\ToggleButtons::make('covid_vaccine_certificate')->label('Covid Vaccine Certificate')->grouped()->boolean(),
                                Forms\Components\ToggleButtons::make('gender')->options(['male' => 'male', 'female' => 'female', 'other' => 'other'])->required()->inline()->grouped(),
                                Forms\Components\KeyValue::make('emergency_contact')->columnSpanFull()->label('Emergency Contact')->keyLabel('title')
                            ])->columns(2),

                        Forms\Components\Wizard\Step::make('Salary information')
                            ->schema([
                                Forms\Components\Select::make('department_id')->createOptionForm([
                                    Forms\Components\TextInput::make('title')
                                        ->required()
                                        ->maxLength(255)->columnSpanFull(),
                                    Forms\Components\Textarea::make('description')->columnSpanFull()
                                ])
                                    ->createOptionUsing(function (array $data,Get $get): int {
                                        return Department::query()->create([
                                            'title' => $data['title'],
                                            'description' => $data['description'],
                                            'company_id' => $get('company_id')
                                        ])->getKey();
                                    })->label('Department')->options(fn(Get $get)=>Department::query()->where('company_id', $get('company_id'))->pluck('title', 'id'))
                                    ->required()->searchable()->preload(),

                                Forms\Components\Select::make('position_id')
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('title')
                                            ->required()->columnSpanFull()
                                            ->maxLength(255),
                                        Forms\Components\FileUpload::make('document')->columnSpanFull(),
                                        Forms\Components\Textarea::make('description')
                                            ->columnSpanFull(),
                                    ])
                                    ->createOptionUsing(function (array $data,Get $get): int {
                                        return Position::query()->create([
                                            'title' => $data['title'],
                                            'description' => $data['description'],
                                            'document' => $data['document'],
                                            'company_id' => $get('company_id')
                                        ])->getKey();
                                    })
                                    ->label('Designation')->options(fn(Get $get)=> Position::query()->where('company_id', $get('company_id'))->pluck('title', 'id'))->searchable()->preload()
                                    ->required(),

                                Forms\Components\Select::make('duty_id')->label('Duty Type')->options(fn(Get $get)=> Duty::query()->where('company_id', $get('company_id'))->pluck('title', 'id'))->searchable()->preload()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('title')
                                            ->required()->columnSpanFull()
                                            ->maxLength(255),
                                        Forms\Components\Textarea::make('description')
                                            ->columnSpanFull(),
                                    ])
                                    ->createOptionUsing(function (array $data,Get $get): int {
                                        return Duty::query()->create([
                                            'title' => $data['title'],
                                            'description' => $data['description'],
                                            'company_id' => $get('company_id')
                                        ])->getKey();
                                    })
                                    ->required(),
                                Forms\Components\Select::make('contract_id')->label('Pay Frequency')->options(fn(Get $get)=> Contract::query()->where('company_id', $get('company_id'))->pluck('title', 'id'))->searchable()->preload()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('title')
                                            ->required()->columnSpanFull()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('day')
                                            ->numeric()
                                            ->required()
                                            ->columnSpanFull(),
                                    ])
                                    ->createOptionUsing(function (array $data,Get $get): int {
                                        return Contract::query()->create([
                                            'title' => $data['title'],
                                            'day' => $data['day'],
                                            'company_id' => $get('company_id')
                                        ])->getKey();
                                    })->required(),
                                Forms\Components\TextInput::make('ID_number')->label('ID Number')->required(),
                                Forms\Components\Select::make('type_of_ID')->label('Type Of ID')->required()->searchable()->options(['New' => 'New', 'Renewal' => 'Renewal', 'Mutilated' => 'Mutilated', 'Loss' => 'Loss', 'Theft' => 'Theft']),
                                Forms\Components\Select::make('card_status')->label('Card Status')->required()->searchable()->options(['National Staff' => 'National Staff', 'International Staff' => 'International Staff', 'National Contractor' => 'National Contractor', 'International Contractor' => 'International Contractor', 'VIP' => 'VIP', 'International Resident' => 'International Resident']),
                                Forms\Components\Select::make('immunization')->label('Immunization')->searchable()->options(['Hepa A' => 'Hepa A', 'Hepa B' => 'Hepa B', 'Thyphoid' => 'Thyphoid', 'NA' => 'NA']),
                                Forms\Components\DatePicker::make('joining_date')->required(),
                                Forms\Components\DatePicker::make('leave_date')->label('Ending Date'),
                                Forms\Components\TextInput::make('base_salary')->label('Base Salary' )->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)
                                    ->default(0)
                                    ->numeric(),
                                Forms\Components\Select::make('benefits')->relationship('benefits', 'title')->label('Allowance')
                                    ->createOptionForm([
                                        Forms\Components\Section::make([
                                            Forms\Components\TextInput::make('title')->required()->maxLength(255),
                                            Forms\Components\ToggleButtons::make('price_type')->inline()->grouped()->options(['0' => 'Price $', '1' => 'Percent %'])->default(0)->live(),
                                            TextInput::make('percent')->columnSpanFull()->maxValue(100)->minValue(1)->suffix('%')->visible(fn(Get $get) => $get('price_type'))->requiredIf('price_type', '1'),
                                            Forms\Components\TextInput::make('amount')->columnSpanFull()->hidden(fn(Get $get) => $get('price_type'))->suffixIcon('cash')->suffixIconColor('success')->minValue(1)->requiredIf('price_type', '0')->numeric()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
                                            Forms\Components\ToggleButtons::make('type')->inline()->grouped()->required()->options(['allowance' => 'Allowance', 'deduction' => 'Deduction']),
                                            Forms\Components\ToggleButtons::make('on_change')->inline()->grouped()->label('On Change')->required()->options(['base_salary' => ' Basic Salary', 'gross' => ' Gross']),
                                        ])->columns(2)
                                    ])
                                ->createOptionUsing(function (array $data,Get $get): int {
                                        return Benefit::query()->create([
                                            'title' => $data['title'],
                                            'amount' => $data['amount'] ?? 0,
                                            'percent' => $data['percent'] ?? 0,
                                            'type' => $data['type'],
                                            'on_change' => $data['on_change'],
                                            'company_id' => $get('company_id')
                                        ])->getKey();
                                    })
                                    ->label('Allowance/Deduction')->pivotData(fn(Get $get)=>[
                                        'company_id' => $get('company_id'),
                                    ])->live()->afterStateUpdated(static function (Forms\Get $get, Forms\Set $set) {
                                    })->multiple()->preload()->options(function (Forms\Get $get ) {
                                        $options = Benefit::query()->where('company_id', $get('company_id'))->get();
                                        $data = [];
                                        foreach ($options as $option) {
                                            $data[$option->id] = $option->title . "(" . $option->type . ")";
                                        }
                                        return $data;
                                    }),
                                Forms\Components\Hidden::make('benefit_salary')->default(0),
                                Forms\Components\Section::make('')->schema([
                                    Forms\Components\Repeater::make('Documents Attachment')->label('Documents Attachment')->relationship('documents')->schema([
                                        Forms\Components\TextInput::make('title')->required(),
                                        Forms\Components\FileUpload::make('file')->required()
                                    ])->mutateRelationshipDataBeforeCreateUsing(function (array $data,Get $get): array {
                                        $data['company_id'] = $get('company_id');
                                        return $data;
                                    })->columns(2)
                                ])
                            ])->columns(2),
                        Forms\Components\Wizard\Step::make('Bank Information')
                            ->schema([
                                Forms\Components\TextInput::make('cart')->label('Account Number')->maxLength(255),
                                Forms\Components\TextInput::make('bank')->maxLength(255),
                                Forms\Components\TextInput::make('branch')->label('Branch')->maxLength(255),
                                Forms\Components\TextInput::make('tin')->label('TIN')->maxLength(255),
                            ])->columns(2),
                        Forms\Components\Wizard\Step::make('Address')
                            ->schema([
                                Forms\Components\Section::make([
                                    Forms\Components\Select::make('country')
                                        ->options(getCountry())->searchable()->preload(),
                                    Forms\Components\TextInput::make('state')->label('State/Province')
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('city')
                                        ->maxLength(255),
                                ])->columns(3),
                                Forms\Components\TextInput::make('post_code')->columnSpanFull()->label('Zip/Postal Code')->nullable()->maxLength(100),
                                Forms\Components\Textarea::make('address')->maxLength(255)->columnSpanFull(),
                                Forms\Components\Textarea::make('address2')->label('Address 2')->columnSpanFull()->maxLength(250),
                            ])->columns(2),
                    ])->columnSpanFull()
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('setPassword')->label('Reset Password')->form([
                    Forms\Components\TextInput::make('password')->required()->autocomplete(false)
                ])->requiresConfirmation()->action(function ($record, $data) {
                    $record->update(['password' => $data['password']]);
                    Notification::make('success')->success()->title('Submitted Successfully')->send();
                })->icon('heroicon-s-lock-closed')->color('warning'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
