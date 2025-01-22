<?php

namespace App\Filament\Resources;

use App\Enums\BoodGroup;
use App\Enums\GenderEnum;
use App\Filament\Resources\EmployeeResource\Pages;
use App\Filament\Resources\EmployeeResource\RelationManagers;
use App\Models\Benefit;
use App\Models\Company;
use App\Models\Contract;
use App\Models\Department;
use App\Models\Duty;
use App\Models\Employee;
use App\Models\Position;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Hamcrest\Core\Set;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;
    protected static ?int $navigationSort = -5;
    protected static ?string $navigationGroup = 'Human Resource';



    protected static ?string $navigationIcon = 'heroicon-c-user-group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Information')
                        ->schema([
                            Forms\Components\FileUpload::make('pic')->label('Profile Picture')
                                ->image()->columnSpanFull()->imageEditor()->extraAttributes(['style' => 'width:150px!important;border-radius:10px !important']),
                            Forms\Components\TextInput::make('fullName')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\DatePicker::make('birthday')->label('Date Birthday'),
                            Forms\Components\TextInput::make('phone_number')->tel()->required()->maxLength(255),
                            Forms\Components\TextInput::make('emergency_phone_number')->tel()->maxLength(255),
                            Forms\Components\TextInput::make('NIC')->label('NIC')->columnSpanFull()->maxLength(255),
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
                            Forms\Components\ToggleButtons::make('gender')->options(['male'=>'male','female'=>'female','other'=>'other'])->required()->inline()->grouped(),

                        ])->columns(2),

                    Forms\Components\Wizard\Step::make('Salary information')
                        ->schema([
                            Forms\Components\Select::make('department_id')->createOptionForm([
                                Forms\Components\TextInput::make('title')
                                    ->required()
                                    ->maxLength(255)->columnSpanFull(),
                                    Forms\Components\Select::make('company_id')->options(Company::all()->pluck('title','id')),
                                Forms\Components\Textarea::make('description')->columnSpanFull()
                            ])
                                ->createOptionUsing(function (array $data): int {
                                    return Department::query()->create([
                                        'title' => $data['title'],
                                        'description' => $data['description'],
                                       'company_id' => $data['company_id']
                                    ])->getKey();
                                })->label('Department')
                                ->options(Department::query()->pluck('title', 'id'))
                                ->required()->searchable()->preload(),

                            Forms\Components\Select::make('position_id')
                                ->createOptionForm([
                                    Forms\Components\TextInput::make('title')
                                    ->required()->columnSpanFull()
                                    ->maxLength(255),
                                    Forms\Components\Select::make('company_id')->options(Company::all()->pluck('title','id')),
                                    Forms\Components\FileUpload::make('document')->columnSpanFull(),
                                    Forms\Components\Textarea::make('description')
                                        ->columnSpanFull(),
                                ])
                                ->createOptionUsing(function (array $data): int {
                                    return Position::query()->create([
                                        'title' => $data['title'],
                                        'description' => $data['description'],
                                        'document' => $data['document'],
                                        'company_id' => $data['company_id']
                                    ])->getKey();
                                })
                                ->label('Position')->options(Position::query()->pluck('title', 'id'))->searchable()->preload()
                                ->required(),

                            Forms\Components\Select::make('duty_id')->label('Duty Type')->options(Duty::query()->pluck('title', 'id'))->searchable()->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('title')
                                    ->required()->columnSpanFull()
                                    ->maxLength(255),
                                    Forms\Components\Select::make('company_id')->options(Company::all()->pluck('title','id')),
                                Forms\Components\Textarea::make('description')
                                    ->columnSpanFull(),
                            ])
                            ->createOptionUsing(function (array $data): int {
                                return Duty::query()->create([
                                    'title' => $data['title'],
                                    'description' => $data['description'],
                                    'company_id' => $data['company_id']
                                ])->getKey();
                            })
                                ->required(),
                            Forms\Components\Select::make('contract_id')->label('Pay Frequency')->options(Contract::query()->pluck('title', 'id'))->searchable()->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('title')
                                    ->required()->columnSpanFull()
                                    ->maxLength(255),
                                    Forms\Components\Select::make('company_id')->options(Company::all()->pluck('title','id')),
                                Forms\Components\TextInput::make('day')
                                ->numeric()
                                    ->columnSpanFull(),
                            ])
                            ->createOptionUsing(function (array $data): int {
                                return Contract::query()->create([
                                    'title' => $data['title'],
                                    'day' => $data['day'],
                                    'company_id' => $data['company_id']
                                ])->getKey();
                            })
                                ->required(),
                            Forms\Components\DatePicker::make('joining_date')
                                ->required(),
                            Forms\Components\DatePicker::make('leave_date')->label('Termination Date'),
                            Forms\Components\TextInput::make('base_salary')->label('Base Salary(USD)')->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)->default(0)->numeric(),
                            Forms\Components\Select::make('benefits')->label('Allowance/Deduction')->live()->afterStateUpdated(static function (Forms\Get $get, Forms\Set $set) {$benefits = Benefit::query()->whereIn('id', $get('benefits'))->sum('amount');$set('benefit_salary', $benefits);})->multiple()->preload()->options(function () {$options =  Benefit::query()->where('company_id',getCompany()->id)->get();$data = [];
                                foreach ($options as $option) {
                                    $data[$option->id] = $option->title . "(" . $option->type . ")";
                                }
                                return $data;
                            }),
                            //                            Forms\Components\Placeholder::make('benefits')
                            //                                ->content(function (Forms\Get $get) {
                            //                                    $benefits = Benefit::query()->whereIn('id', $get('benefits'))->get();
                            //                                    $content = '';
                            //                                    foreach ($benefits as $benefit) {
                            //                                        $content .= "<div style='display: flex;border: 2px solid black;text-align: center    '>
                            //                                                    <p style='width: 50%;border: 2px solid black'>$benefit->title</p>
                            //                                                    <p style='width: 50%;border: 2px solid black'>$benefit->amount $</p>
                            //                                                    </div> ";
                            //                                    }
                            //                                    return new HtmlString($content);
                            //                                }),
                            //                            Forms\Components\Placeholder::make('benefits')
                            //                                ->content(function (Forms\Get $get) {
                            //                                    $benefits = Benefit::query()->whereIn('id', $get('benefits'))->get();
                            //                                    $content = '';
                            //                                    foreach ($benefits as $benefit) {
                            //                                        $content .= "<div style='display: flex;border: 2px solid black;text-align: center    '>
                            //                                                    <p style='width: 50%;border: 2px solid black'>$benefit->title</p>
                            //                                                    <p style='width: 50%;border: 2px solid black'>$benefit->amount $</p>
                            //                                                    </div> ";
                            //                                    }
                            //                                    return new HtmlString($content);
                            //                                }),
                            Forms\Components\Hidden::make('benefit_salary')->default(0),
                            Forms\Components\Section::make('')->schema([
                                Forms\Components\Repeater::make('documents')->relationship('documents')->schema([
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
                            Forms\Components\TextInput::make('cart')->label('Account Number')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('bank')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('branch')->label('Branch')
                                ->maxLength(255),
                            Forms\Components\TextInput::make('tin')->label('TIN')
                                ->maxLength(255),


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
                            Forms\Components\TextInput::make('post_code')->label('Zip/Postal Code')->nullable()->maxLength(100),
                            Forms\Components\Textarea::make('address')->maxLength(255)->columnSpanFull(),
                            Forms\Components\Textarea::make('address2')->label('Address 2')->columnSpanFull()->maxLength(250),
                        ])->columns(2),
                    Forms\Components\Wizard\Step::make('Account')
                        ->schema([
                            Forms\Components\Section::make([
                                Forms\Components\Section::make([
                                    Forms\Components\TextInput::make('email')->email()->unique('users', 'email')->required()->maxLength(255),
                                    Forms\Components\TextInput::make('password')->hintAction(Forms\Components\Actions\Action::make('generate_password')->action(function (Forms\Set $set) {
                                        $password = Str::password(8);
                                        $set('password', $password);
                                        $set('password_confirmation', $password);
                                    }))->dehydrated(fn(?string $state): bool => filled($state))->revealable()->required(fn(string $operation): bool => $operation === 'create')->configure()->same('password_confirmation')->password(),
                                    Forms\Components\TextInput::make('password_confirmation')->revealable()->required(fn(string $operation): bool => $operation === 'create')->password()
                                ])->columns(3)->visible(fn($operation) => $operation === "create"),
                                Forms\Components\Fieldset::make('user')->relationship('user')->schema([
                                    Forms\Components\TextInput::make('email')->email()->unique('users', 'email', ignoreRecord: true)->required()->maxLength(255),
                                    Forms\Components\TextInput::make('password')->hintAction(Forms\Components\Actions\Action::make('generate_password')->action(function (Forms\Set $set) {
                                        $password = Str::password(8);
                                        $set('password', $password);
                                        $set('password_confirmation', $password);
                                    }))->dehydrated(fn(?string $state): bool => filled($state))->revealable()->required(fn(string $operation): bool => $operation === 'create')->configure()->same('password_confirmation')->password(),
                                    Forms\Components\TextInput::make('password_confirmation')->revealable()->required(fn(string $operation): bool => $operation === 'create')->password()
                                ])->columns(3)->visible(fn($operation) => $operation === "edit"),
                            ])->columns(2),
                        ])
                ])->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->searchable()
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\ImageColumn::make('pic')->label('profile picture')->width(50)->height(50)->extraAttributes(['style' => 'border-radius:50px!important']),
                Tables\Columns\TextColumn::make('fullName')->sortable(),
                Tables\Columns\TextColumn::make('gender')->state(fn($record)=> $record->gender ?  "Male" :'Female')->sortable(),
                Tables\Columns\TextColumn::make('phone_number')->sortable(),
                Tables\Columns\TextColumn::make('birthday')->date()->sortable(),
                Tables\Columns\TextColumn::make('duty.title')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('base_salary')->numeric()->sortable()->badge(),
                Tables\Columns\TextColumn::make('department.title')->color('aColor')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('position.title')->label('Designation')->sortable(),
            //    Tables\Columns\TextColumn::make('payrolls_count')->counts('payrolls')->badge()->url(fn($record): string => (PayrollResource::getUrl('index', ['tableFilters[employee_id][value]' => $record->id])))->sortable(),

            ])
            ->filters([

                SelectFilter::make('department_id')->searchable()->preload()->options(Department::all()->pluck('title', 'id'))
                    ->label('Department'),


                    SelectFilter::make('duty_id')->searchable()->preload()->options(Duty::all()->pluck('title', 'id'))

                    ->label('duty'),

                TernaryFilter::make('gender')->searchable()->preload()->trueLabel('Man')->falseLabel('Woman'),

                DateRangeFilter::make('birthday'),
                DateRangeFilter::make('joining_date'),
                DateRangeFilter::make('leave_date'),
                Filter::make('base_salary')
                    ->form([
                        Forms\Components\Section::make([
                            TextInput::make('min')->label('Min Base Salary')
                                ->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)
                                ->numeric(),

                            TextInput::make('max')->label('Max Base Salary')
                                ->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)
                                ->numeric(),
                        ])->columns()
                    ])->columnSpanFull()
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min'],
                                fn(Builder $query, $date): Builder => $query->where('base_salary', '>=', str_replace(',','',$date)),
                            )
                            ->when(
                                $data['max'],
                                fn(Builder $query, $date): Builder => $query->where('base_salary', '<=', str_replace(',','',$date)),
                            );
                    }),


            ], getModelFilter())
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}
