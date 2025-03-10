<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ITemployeeResource\Pages;
use App\Filament\Admin\Resources\ITemployeeResource\RelationManagers;
use App\Models\Employee;
use App\Models\ITemployee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use App\Filament\Admin\Resources\EmployeeResource\RelationManagers\AssetEmployeeItemsRelationManager;
use App\Filament\Admin\Resources\EmployeeResource\RelationManagers\LeavesRelationManager;
use App\Filament\Admin\Resources\EmployeeResource\RelationManagers\OverTimesRelationManager;
use App\Filament\Admin\Resources\EmployeeResource\RelationManagers\PayrollsRelationManager;
use App\Models\Benefit;
use App\Models\CompanyUser;
use App\Models\Contract;
use App\Models\Department;
use App\Models\Duty;
use App\Models\Position;
use App\Models\Structure;
use App\Models\User;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Support\Enums\IconSize;
use Filament\Support\RawJs;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use OpenSpout\Common\Entity\Style\CellAlignment;
use Spatie\Permission\Models\Role;
class ITemployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $label='User Creation';
    protected static ?string $pluralLabel='Users';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationGroup = 'IT Management';
    protected static ?string $navigationIcon = 'heroicon-m-user-group';

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'user',
            'password',
            'role',
            'email',
            'clearance'
        ];
    }
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Information')
                        ->schema([
                            Forms\Components\FileUpload::make('pic')->label('Profile Picture')->image()->columnSpan(1)->imageEditor()->extraAttributes(['style' => 'width:150px!important;border-radius:10px !important']),
                            Forms\Components\FileUpload::make('signature_pic')->label('Signature')->image()->columnSpan(1)->imageEditor()->extraAttributes(['style' => 'width:150px!important;border-radius:10px !important']),
                            Forms\Components\TextInput::make('fullName')->required()->maxLength(255),
                            Forms\Components\DatePicker::make('birthday')->label('Date Birthday'),
                            Forms\Components\TextInput::make('phone_number')->tel()->required()->maxLength(255),
                            Forms\Components\TextInput::make('NIC')->label('NIC')->maxLength(255),
                            Forms\Components\Select::make('marriage')->label('Marital Status')->options(['divorced' => 'Divorced', 'widowed' => 'Widowed', 'married' => 'Married', 'single' => 'Single',])->searchable()->preload(),
                            Forms\Components\TextInput::make('count_of_child')->label('Number Of Child')
                                ->numeric()
                                ->default(0),
                            Forms\Components\Section::make([
                                Forms\Components\TextInput::make('ID_number')->label('ID Number')->required(),
                                Forms\Components\Select::make('type_of_ID')->label('Type Of ID')->required()->searchable()->options(['New' => 'New', 'Renewal' => 'Renewal', 'Mutilated' => 'Mutilated', 'Loss' => 'Loss', 'Theft' => 'Theft']),
                                Forms\Components\Select::make('card_status')->label('Card Status')->required()->searchable()->options(['National Staff' => 'National Staff', 'International Staff' => 'International Staff', 'National Contractor' => 'National Contractor', 'International Contractor' => 'International Contractor', 'VIP' => 'VIP', 'International Resident' => 'International Resident']),
                                Forms\Components\Select::make('immunization')->label('Immunization')->searchable()->options(['Hepa A' => 'Hepa A', 'Hepa B' => 'Hepa B', 'Thyphoid' => 'Thyphoid', 'NA' => 'NA'])->multiple(),
                            ])->columns(),
                            Forms\Components\Select::make('blood_group')
                                ->options([
                                    "O negative"=>"O negative",
                                    "O positive"=>"O positive",
                                    "A negative"=>"A negative",
                                    "A positive"=>"A positive",
                                    "B negative"=>"B negative",
                                    "B positive"=>"B positive",
                                    "AB negative"=>"AB negative",
                                    "AB positive"=>"AB positive",
                                ])->searchable(),
                            Forms\Components\ToggleButtons::make('covid_vaccine_certificate')->label('Covid Vaccine Certificate')->grouped()->boolean(),
                            Forms\Components\ToggleButtons::make('gender')->options(['male' => 'Male', 'female' => 'Female', 'other' => 'Other'])->required()->inline()->grouped(),
                            Forms\Components\Repeater::make('emergency_contact')->columnSpanFull()->label('Emergency Contact')->schema([
                                TextInput::make('name')->required(),
                                TextInput::make('relation')->required(),
                                TextInput::make('number')->required(),
                            ])->columns(3)
                        ])->columns(2),

                    Forms\Components\Wizard\Step::make('Salary information')
                        ->schema([
                            Forms\Components\Select::make('department_id')->createOptionForm([
                                Forms\Components\TextInput::make('title')
                                    ->required()
                                    ->maxLength(255)->columnSpanFull(),
                                Forms\Components\Textarea::make('description')->columnSpanFull()
                            ])
                                ->createOptionUsing(function (array $data): int {
                                    return Department::query()->create([
                                        'title' => $data['title'],
                                        'description' => $data['description'],
                                        'company_id' => getCompany()->id
                                    ])->getKey();
                                })->label('Department')->options(Department::query()->where('company_id', getCompany()->id)->pluck('title', 'id'))
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
                                ->createOptionUsing(function (array $data): int {
                                    return Position::query()->create([
                                        'title' => $data['title'],
                                        'description' => $data['description'],
                                        'document' => $data['document'],
                                        'company_id' => getCompany()->id
                                    ])->getKey();
                                })
                                ->label('Designation')->options(Position::query()->where('company_id', getCompany()->id)->pluck('title', 'id'))->searchable()->preload()
                                ->required(),

                            Forms\Components\Select::make('duty_id')->label('Duty Type')->options(Duty::query()->where('company_id', getCompany()->id)->pluck('title', 'id'))->searchable()->preload()
                                ->createOptionForm([
                                    Forms\Components\TextInput::make('title')
                                        ->required()->columnSpanFull()
                                        ->maxLength(255),
                                    Forms\Components\Textarea::make('description')
                                        ->columnSpanFull(),
                                ])
                                ->createOptionUsing(function (array $data): int {
                                    return Duty::query()->create([
                                        'title' => $data['title'],
                                        'description' => $data['description'],
                                        'company_id' => getCompany()->id
                                    ])->getKey();
                                })
                                ->required(),
                            Forms\Components\Select::make('contract_id')->label('Pay Frequency')->options(Contract::query()->where('company_id', getCompany()->id)->pluck('title', 'id'))->searchable()->preload()
                                ->createOptionForm([
                                    Forms\Components\TextInput::make('title')
                                        ->required()->columnSpanFull()
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('day')
                                        ->numeric()
                                        ->required()
                                        ->columnSpanFull(),
                                ])
                                ->createOptionUsing(function (array $data): int {
                                    return Contract::query()->create([
                                        'title' => $data['title'],
                                        'day' => $data['day'],
                                        'company_id' => getCompany()->id
                                    ])->getKey();
                                })->required(),
                            Forms\Components\Select::make('warehouse_id')->live()->label('Duty Location(Building)')->options(getCompany()->warehouses()->pluck('title', 'id'))->searchable()->preload(),
                            SelectTree::make('structure_id')->label('Address')->searchable()->label('Duty Location(Room)')->enableBranchNode()->defaultOpenLevel(2)->model(Structure::class)->relationship('parent', 'title', 'parent_id',modifyQueryUsing: function($query, Forms\Get $get){
                                return $query->where('warehouse_id', $get('warehouse_id'));
                            }),
                            Forms\Components\DatePicker::make('joining_date')->required(),
                            Forms\Components\DatePicker::make('leave_date')->label('Ending Date'),
                            Forms\Components\TextInput::make('base_salary')->required()->label('Base Salary' . '(' .defaultCurrency()?->symbol . ")")->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)->default(0)->numeric(),
                            Forms\Components\TextInput::make('daily_salary')->label('Daily Salary' . '(' . defaultCurrency()?->symbol . ")")->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->minValue(0),

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
                                ->createOptionUsing(function (array $data): int {
                                    return Benefit::query()->create([
                                        'title' => $data['title'],
                                        'amount' => $data['amount'] ?? 0,
                                        'percent' => $data['percent'] ?? 0,
                                        'type' => $data['type'],
                                        'on_change' => $data['on_change'],
                                        'company_id' => getCompany()->id
                                    ])->getKey();
                                })
                                ->label('Allowance/Deduction')->pivotData([
                                    'company_id' => getCompany()->id,
                                ])->columnSpanFull()->live()->afterStateUpdated(static function (Forms\Get $get, Forms\Set $set) {})->multiple()->preload()->options(function () {
                                    $options = Benefit::query()->where('company_id', getCompany()->id)->get();
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
                                    Forms\Components\FileUpload::make('file')->downloadable()->required()
                                ])->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                                    $data['company_id'] = Filament::getTenant()->id;
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
                ])->skippable()->columnSpanFull()
            ]);
    }

    public static function getForm()
    {
        return [


            Forms\Components\Wizard\Step::make('Information')
                ->schema([

                    Forms\Components\FileUpload::make('pic')->label('Profile Picture')->image()->columnSpan(1)->imageEditor()->extraAttributes(['style' => 'width:150px!important;border-radius:10px !important']),
                    Forms\Components\FileUpload::make('signature_pic')->label('Signature')->image()->columnSpan(1)->imageEditor()->extraAttributes(['style' => 'width:150px!important;border-radius:10px !important']),

                    Forms\Components\TextInput::make('fullName')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\DatePicker::make('birthday')->label('Date Birthday'),
                    Forms\Components\TextInput::make('phone_number')->required()->maxLength(255),
                    Forms\Components\TextInput::make('email')->email()->required()->maxLength(255),
                    Forms\Components\TextInput::make('NIC')->label('NIC')->maxLength(255),
                    Forms\Components\ToggleButtons::make('gender')->options(['male' => 'Male', 'female' => 'Female', 'other' => 'Other'])->required()->inline()->grouped(),
                    Forms\Components\Select::make('marriage')->label('Marital Status')->options(['divorced' => 'Divorced', 'widowed' => 'Widowed', 'married' => 'Married', 'single' => 'Single',])->searchable()->preload(),
                    Forms\Components\TextInput::make('count_of_child')->label('Number Of Child')
                        ->numeric()
                        ->default(0),
                    Forms\Components\Section::make([
                        Forms\Components\TextInput::make('ID_number')->label('ID Number')->required(),
                        Forms\Components\Select::make('type_of_ID')->label('Type Of ID')->required()->searchable()->options(['New' => 'New', 'Renewal' => 'Renewal', 'Mutilated' => 'Mutilated', 'Loss' => 'Loss', 'Theft' => 'Theft']),
                        Forms\Components\Select::make('card_status')->label('Card Status')->required()->searchable()->options(['National Staff' => 'National Staff', 'International Staff' => 'International Staff', 'National Contractor' => 'National Contractor', 'International Contractor' => 'International Contractor', 'VIP' => 'VIP', 'International Resident' => 'International Resident']),
                        Forms\Components\Select::make('immunization')->label('Immunization')->searchable()->options(['Hepa A' => 'Hepa A', 'Hepa B' => 'Hepa B', 'Thyphoid' => 'Thyphoid', 'NA' => 'NA'])->multiple(),
                    ])->columns(),
                    Forms\Components\Select::make('blood_group')
                        ->options([
                            "O negative"=>"O negative",
                            "O positive"=>"O positive",
                            "A negative"=>"A negative",
                            "A positive"=>"A positive",
                            "B negative"=>"B negative",
                            "B positive"=>"B positive",
                            "AB negative"=>"AB negative",
                            "AB positive"=>"AB positive",
                        ])->searchable(),
                    Forms\Components\ToggleButtons::make('covid_vaccine_certificate')->label('Covid Vaccine Certificate')->grouped()->boolean(),
                    Forms\Components\Repeater::make('emergency_contact')->columnSpanFull()->label('Emergency Contact')->schema([
                        TextInput::make('name')->required(),
                        TextInput::make('relation')->required(),
                        TextInput::make('number')->required(),
                    ])->columns(3)
                ])->columns(2),

            Forms\Components\Wizard\Step::make('Salary information')
                ->schema([
                    Forms\Components\Select::make('department_id')->createOptionForm([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)->columnSpanFull(),
                        Forms\Components\Textarea::make('description')->columnSpanFull()
                    ])
                        ->createOptionUsing(function (array $data): int {
                            return Department::query()->create([
                                'title' => $data['title'],
                                'description' => $data['description'],
                                'company_id' => getCompany()->id
                            ])->getKey();
                        })->label('Department')->options(Department::query()->where('company_id', getCompany()->id)->pluck('title', 'id'))
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
                        ->createOptionUsing(function (array $data): int {
                            return Position::query()->create([
                                'title' => $data['title'],
                                'description' => $data['description'],
                                'document' => $data['document'],
                                'company_id' => getCompany()->id
                            ])->getKey();
                        })
                        ->label('Position')->options(Position::query()->where('company_id', getCompany()->id)->pluck('title', 'id'))->searchable()->preload()
                        ->required(),

                    Forms\Components\Select::make('duty_id')->label('Duty Type')->options(Duty::query()->where('company_id', getCompany()->id)->pluck('title', 'id'))->searchable()->preload()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('title')
                                ->required()->columnSpanFull()
                                ->maxLength(255),
                            Forms\Components\Textarea::make('description')
                                ->columnSpanFull(),
                        ])
                        ->createOptionUsing(function (array $data): int {
                            return Duty::query()->create([
                                'title' => $data['title'],
                                'description' => $data['description'],
                                'company_id' => getCompany()->id
                            ])->getKey();
                        })
                        ->required(),
                    Forms\Components\Select::make('contract_id')->label('Pay Frequency')->options(Contract::query()->where('company_id', getCompany()->id)->pluck('title', 'id'))->searchable()->preload()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('title')
                                ->required()->columnSpanFull()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('day')
                                ->numeric()
                                ->required()
                                ->columnSpanFull(),
                        ])
                        ->createOptionUsing(function (array $data): int {
                            return Contract::query()->create([
                                'title' => $data['title'],
                                'day' => $data['day'],
                                'company_id' => getCompany()->id
                            ])->getKey();
                        })->required(),
                    Forms\Components\Select::make('warehouse_id')->live()->label('Duty Location(Building) ')->options(getCompany()->warehouses()->pluck('title', 'id'))->searchable()->preload(),
                    SelectTree::make('structure_id')->label('Address')->searchable()->label('Duty Location(Room)')->enableBranchNode()->defaultOpenLevel(2)->model(Structure::class)->relationship('parent', 'title', 'parent_id',modifyQueryUsing: function($query, Forms\Get $get){
                        return $query->where('warehouse_id', $get('warehouse_id'));
                    }),
                    Forms\Components\DatePicker::make('joining_date')->required(),
                    Forms\Components\DatePicker::make('leave_date')->label('Ending Date'),
                    Forms\Components\TextInput::make('base_salary')->required()->label('Base Salary' . '(' . defaultCurrency()?->symbol . ")")->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->minValue(0),
                    Forms\Components\TextInput::make('daily_salary')->label('Daily Salary' . '(' . defaultCurrency()?->symbol . ")")->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)
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
                        ->createOptionUsing(function (array $data): int {
                            return Benefit::query()->create([
                                'title' => $data['title'],
                                'amount' => $data['amount'] ?? 0,
                                'percent' => $data['percent'] ?? 0,
                                'type' => $data['type'],
                                'on_change' => $data['on_change'],
                                'company_id' => getCompany()->id
                            ])->getKey();
                        })
                        ->label('Allowance/Deduction')->pivotData([
                            'company_id' => getCompany()->id,
                        ])->columnSpanFull()->live()->afterStateUpdated(static function (Forms\Get $get, Forms\Set $set) {})->multiple()->preload()->options(function () {
                            $options = Benefit::query()->where('company_id', getCompany()->id)->get();
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
                        ])->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                            $data['company_id'] = Filament::getTenant()->id;
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
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->searchable()
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\ImageColumn::make('pic')->defaultImageUrl(fn($record)=>$record->gender==="male" ?  asset('img/user.png') :asset('img/female.png'))->alignLeft()->label('Profile Picture')->width(50)->height(50)->extraAttributes(['style' => 'border-radius:50px!important']),
                Tables\Columns\TextColumn::make('fullName')->sortable()->alignLeft()->searchable(),
                Tables\Columns\TextColumn::make('gender')->state(function($record){
                    if ($record->gender==="male"){
                        return "Male";
                    }elseif ($record->gender==="female"){
                        return "Female";
                    }else{
                        return  "Other";
                    }
                } )->alignLeft()->sortable(),
                Tables\Columns\TextColumn::make('phone_number')->alignLeft()->sortable()->searchable(),
                Tables\Columns\TextColumn::make('duty.title')->alignLeft()->numeric()->sortable()->searchable(),
                Tables\Columns\TextColumn::make('department.title')->alignLeft()->color('aColor')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('position.title')->alignLeft()->label('Position')->sortable(),
                //                Tables\Columns\TextColumn::make('payrolls_count')->counts('payrolls')->badge()->url(fn($record): string => (PayrollResource::getUrl('index', ['tableFilters[employee_id][value]' => $record->id])))->sortable(),

            ])
            ->filters([

                SelectFilter::make('department_id')->searchable()->preload()->options(Department::where('company_id', getCompany()->id)->get()->pluck('title', 'id'))
                    ->label('Department'),
                    SelectFilter::make('position_id')->searchable()->preload()->options(Position::where('company_id', getCompany()->id)->get()->pluck('title', 'id'))
                    ->label('Designation'),


                SelectFilter::make('duty_id')->searchable()->preload()->options(Duty::where('company_id', getCompany()->id)->get()->pluck('title', 'id'))
                    ->label('duty'),

                TernaryFilter::make('gender')->searchable()->preload()->trueLabel('Man')->falseLabel('Woman'),

                DateRangeFilter::make('birthday'),
                DateRangeFilter::make('joining_date'),
                DateRangeFilter::make('leave_date'),



            ], getModelFilter())
            ->actions([

                Tables\Actions\EditAction::make(),
                // Tables\Actions\ViewAction::make(),
                // Tables\Actions\Action::make('pdf')->tooltip('Print')->icon('heroicon-s-printer')->iconSize(IconSize::Medium)->label('')
                //     ->url(fn($record) => route('pdf.employee', ['id' => $record->id]))->openUrlInNewTab(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('makeUser')->icon('heroicon-o-user-circle')->label('Create User')->form([
                        Forms\Components\Section::make('')->schema([
                            Forms\Components\Select::make('roles')->model(User::class)
                                ->options(Role::query()->where('company_id',getCompany()->id)->pluck('name','id'))
                                ->multiple()
                                ->preload()
                                ->searchable(),
                            Forms\Components\TextInput::make('email')->model(User::class)->email()->unique('users', 'email', ignoreRecord: true)->required()->maxLength(255),
                            Forms\Components\TextInput::make('password')->autocomplete(false)->hintAction(Forms\Components\Actions\Action::make('generate_password')->action(function (Forms\Set $set) {
                                $password = Str::password(8);
                                $set('password', $password);
                                $set('password_confirmation', $password);
                            }))->revealable()->required()->configure()->same('password_confirmation')->password(),
                            Forms\Components\TextInput::make('password_confirmation')->autocomplete(false)->revealable()->required()->password()
                        ])
                    ])->visible(fn($record) => (auth()->user()->can('user_employee')  and $record->user === null))->action(function ($data,$record){
                        $roles = $data['roles'];
                        $rolesWithCompanyId = [];
                        foreach ($roles as $roleId) {
                            $rolesWithCompanyId[$roleId] = ['company_id' => getCompany()->id];
                        }

                        $user = User::query()->create([
                            'name' => $record->fullName,
                            'email' => $data['email'],
                            'password' => $data['password']
                        ]);
                        $user->roles()->attach($rolesWithCompanyId);
                        $record->update(['user_id'=>$user->id]);
                        CompanyUser::query()->create(['user_id'=>$record->user_id,'company_id'=>getCompany()->id]);
                        Notification::make('success')->success()->title('Submitted Successfully')->send();

                    }),
                    Tables\Actions\Action::make('setMail')->visible(fn($record) => $record->user and auth()->user()->can('email_employee')  )->label('Modify Mail')->fillForm(function ($record) {
                        return [
                            'email' => $record->user->email
                        ];
                    })->form(function ($record) {
                        return [
                            Forms\Components\TextInput::make('email')->model(User::class)->email()->unique('users', 'email', ignorable: User::query()->firstWhere('id', $record->user_id), ignoreRecord: true)->required()->maxLength(255),
                        ];
                    })->action(function ($data, $record) {
                        $record->user->update(['email' => $data['email']]);
                        Notification::make('success')->success()->title('Submitted Successfully')->send();
                    })->requiresConfirmation()->icon('heroicon-c-at-symbol')->color('info'),
                    Tables\Actions\Action::make('setPassword')->visible(fn($record) => $record->user and auth()->user()->can('password_employee') )->label('Reset Password')->form([
                        Forms\Components\TextInput::make('password')->required()->autocomplete(false)
                    ])->requiresConfirmation()->action(function ($record, $data) {
                        $record->user->update(['password' => $data['password']]);
                        Notification::make('success')->success()->title('Submitted Successfully')->send();
                    })->icon('heroicon-s-lock-closed')->color('warning'),
                    Tables\Actions\Action::make('setRole')->visible(fn($record) => $record->user and auth()->user()->can('role_employee') )->fillForm(function ($record) {
                        return [
                            'roles' => $record->user->roles->where('company_id',getCompany()->id)->where('is_show',1)->pluck('id')->toArray()
                        ];
                    })->label('Assignee Role')->form([
                        Forms\Components\Select::make('roles')->pivotData(['company_id' => getCompany()->id])->options(getCompany()->roles->where('is_show',1)->pluck('name', 'id'))->multiple()->preload()->searchable(),
                    ])->requiresConfirmation()->action(function ($record, $data) {
                        $user = User::query()->firstWhere('id', $record->user_id);
                        $rolesWithCompanyId = [];

                        if ($user->roles->where('is_show',0)->first()){
                            $rolesWithCompanyId[$user->roles->where('is_show',0)->first()->id]=['company_id' => getCompany()->id];
                        }

                        foreach ($data['roles'] as $roleId) {
                            $rolesWithCompanyId[$roleId] = ['company_id' => getCompany()->id];
                        }
                        $user->roles()->sync($rolesWithCompanyId);
                        Notification::make('success')->success()->title('Submitted Successfully')->send();
                    })->icon('heroicon-s-shield-check')->color('danger'),
                ])->color('warning'),

            ])->actionsColumnLabel('Actions')
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            PayrollsRelationManager::class,
            LeavesRelationManager::class,
            OverTimesRelationManager::class,
            AssetEmployeeItemsRelationManager::class,
          //  RelationManagers\AssetEmployeeRepairRelationManager::class,
        ];
    }

    // public static function getPages(): array
    // {
    //     return [
    //         'index' => Pages\ListEmployees::route('/'),
    //         'create' => Pages\CreateEmployee::route('/create'),
    //         'edit' => Pages\EditEmployee::route('/{record}/edit'),
    //         'view' => Pages\ViewEmployee::route('/{record}'),
    //     ];
    // }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListITemployees::route('/'),
            'create' => Pages\CreateITemployee::route('/create'),
            'edit' => Pages\EditITemployee::route('/{record}/edit'),
        ];
    }
}
