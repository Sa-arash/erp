<?php

namespace App\Filament\Admin\Resources;

use App\Enums\LeaveStatus;
use App\Enums\PayrollStatus;
use App\Filament\Admin\Resources\PayrollResource\Pages;
use App\Models\Account;
use App\Models\Bank_category;
use App\Models\Benefit;
use App\Models\BenefitPayroll;
use App\Models\Contract;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\FinancialPeriod;
use App\Models\Invoice;
use App\Models\Leave;
use App\Models\Overtime;
use App\Models\Payroll;
use App\Models\Transaction;
use Carbon\Carbon;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconSize;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;


class PayrollResource extends Resource
{
    protected static ?string $model = Payroll::class;
    protected static ?string $navigationGroup = 'HR Management';
    protected static ?string $navigationIcon = 'payment';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make([
                    Forms\Components\Select::make('employee_id')->disabled(fn($operation) => $operation === "edit")
                        ->live()->suffixIcon('employee')->suffixIconColor('primary')->label('Employee')->searchable()->preload()->options(Employee::query()->where('company_id', getCompany()->id)->pluck('fullName', 'id'))->required()
                        ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get) {
                            $employee = Employee::query()->with(['department', 'position'])->firstWhere('id', $get('employee_id'));
                            if ($employee) {
                                $amount = $employee->base_salary;
                                $titleDepartment = $employee->department?->title;
                                $titlePosition = $employee->position?->title;
                                $salary = $employee->titlePosition;
                                $set('amount_pay', number_format($amount));
                                $set('department', $titleDepartment);
                                $set('position', $titlePosition);
                                $set('salary', number_format($salary));
                                $set('base', number_format($amount));
                            } else {
                                $set('amount_pay', null);
                                $set('department', null);
                                $set('position', null);
                                $set('salary', null);
                                $set('base', null);
                            }
                        }),
                    Forms\Components\Select::make('year')->disabled(fn($operation) => $operation === "edit")->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                        $month = $get('month') + 1;
                        $year = $get('year') != null ? $get('year') : now()->year;
                        $startDate = Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
                        $endDate = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();
                        $set('start_date', $startDate);
                        $set('end_date', $endDate);
                    })->live()->required()->searchable()->default(now()->year)->options([2024 => 2024, 2025 => 2025, 2026 => 2026, 2027 => 2027, 2028 => 2028, 2029 => 2029, 2030 => 2030]),
                    Forms\Components\Select::make('month')->options([
                        'January',
                        'February',
                        'March',
                        'April',
                        'May',
                        'June',
                        'July',
                        'August',
                        'September',
                        'October',
                        'November',
                        'December'
                    ])->live()->searchable()->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                        $month = $get('month') + 1;
                        $year = $get('year') != null ? $get('year') : now()->year;
                        $startDate = Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
                        $endDate = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();
                        $set('start_date', $startDate);
                        $set('end_date', $endDate);
                    })->required(),
                    Forms\Components\Split::make([
                        TextInput::make('department')->disabled(),
                        TextInput::make('position')->disabled(),
                        TextInput::make('salary')->label('Daily Salary')->disabled(),
                        TextInput::make('base')->label('Monthly Salary')->disabled(),
                    ])->columnSpanFull(),

                    Forms\Components\Hidden::make('start_date')->live()->required()->disabled(fn($operation) => $operation === "edit"),
                    Forms\Components\Hidden::make('end_date')->live()->required()->disabled(fn($operation) => $operation === "edit"),
                    TextInput::make('reference')->hidden()->maxLength(255),
                    Forms\Components\DateTimePicker::make('payment_date')->hidden()->default(now())->required(),
                    Forms\Components\ToggleButtons::make('status')->hidden()->grouped()->options(PayrollStatus::class)->required()->inline(),
                    Forms\Components\Section::make([
                        Forms\Components\Placeholder::make('')->content(function (Forms\Get $get) {
                            $company = getCompany();
                            $employee = Employee::query()->firstWhere('id', $get('employee_id'));
                            if ($employee) {
                                $startDate = $get('start_date');
                                $endDate = $get('end_date');

                                $leaves = Leave::query()->whereHas('typeLeave', function ($query) {
                                    return $query->where('is_payroll', 0);
                                })->where('status', 'accepted')->where('employee_id', $employee->id)->whereBetween('start_leave', [$startDate, $endDate])->whereBetween('end_leave', [$get('start_date'), $get('end_date')])->get();
                                $overtimes = Overtime::query()->where('status', 'accepted')->where('employee_id', $employee->id)->whereBetween('overtime_date', [$startDate, $endDate])->sum('hours');

                                $hoursPay = $employee->daily_salary / $company->daily_working_hours;
                                $totalAllowance = number_format(($overtimes * $hoursPay) * $company->overtime_rate, 2) . $company->currency;
                                $contentOvertime = "
                                <div style='color: green; display: flex; border: 1px solid whitesmoke; text-align: center; width: 48%;'>
                                    <p style='width: 100%; border: 2px solid black;'>Total Overtime</p>
                                    <p style='width: 100%; border: 2px solid black;'>$overtimes H</p>
                                    <p style='width: 100%; border: 2px solid black;'>$totalAllowance</p>
                                </div> ";

                                $totalDay = 0;
                                $total = 0;
                                foreach ($leaves as $leave) {
                                    $totalDay += $leave->days;
                                    $total += ($employee->daily_salary * $leave->days);
                                }

                                $total = number_format($total, 2) . $company->currency;
                                $contentLeave = "
                                <div style='color: red; display: flex   ; border: 1px solid whitesmoke; text-align: center; width: 48%;'>
                                    <p style='width: 100%; border: 2px solid black;'>Total Leave</p>
                                    <p style='width: 100%; border: 2px solid black;'>$totalDay D</p>
                                    <p style='width: 100%; border: 2px solid black;'>$total</p>
                                </div>";

                                $content = "
                                <div style='display: flex; justify-content: space-between; align-items: center;'>
                                    $contentOvertime
                                    $contentLeave
                                </div>";
                                return new HtmlString($content);
                            }
                        })
                    ])->collapsible()
                        ->persistCollapsed(),
                ])->columns(3),
                Forms\Components\Section::make([
                    Forms\Components\Section::make([
                        Forms\Components\Repeater::make('Allowance')->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                            $data['company_id'] = getCompany()->id;
                            return $data;
                        })->label('Allowances')->relationship('itemAllowances')->schema([
                            Forms\Components\Select::make('benefit_id')->required()->label('Allowance')->searchable()->preload()->live()->options(function () {
                                $options = Benefit::query()->where('type', 'allowance')->where('company_id', getCompany()->id)->get();
                                $data = [];
                                foreach ($options as $option) {
                                    $data[$option->id] = $option->title . "(" . $option->type . ")";
                                }
                                return $data;
                            })->afterStateUpdated(function (Forms\Set $set, Forms\Get $get) {
                                $benefit = Benefit::query()->firstWhere('id', $get('benefit_id'));

                                if ($benefit) {
                                    if ($benefit->price_type) {
                                        $set("percent", $benefit->percent);
                                        $set("amount", 0);

                                    } else {
                                        $set("amount", $benefit->amount);
                                        $set("percent", 0);
                                    }

                                } else {
                                    $set("amount", 0);
                                    $set("percent", 0);
                                }
                            })->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                            TextInput::make('amount')->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)->required()->visible(fn($state) => $state > 0)->default(0),
                            TextInput::make('percent')->required()->visible(fn($state) => $state > 0)->default(0),
                        ])->columns(2)->afterStateUpdated(function (Forms\Set $set) {
                            $set('calculation_done', false);
                        })->live(),

                        Forms\Components\Repeater::make('Deduction')->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                            $data['company_id'] = getCompany()->id;
                            return $data;
                        })->label('Deductions')->relationship('itemDeductions')->schema([
                            Forms\Components\Select::make('benefit_id')->afterStateUpdated(function (Forms\Set $set, Forms\Get $get) {
                                $benefit = Benefit::query()->firstWhere('id', $get('benefit_id'));
                                if ($benefit) {
                                    if ($benefit->price_type) {
                                        $set("percent", $benefit->percent);
                                        $set("amount", 0);

                                    } else {
                                        $set("amount", $benefit->amount);
                                        $set("percent", 0);
                                    }

                                } else {
                                    $set("amount", 0);
                                    $set("percent", 0);
                                }
                            })->required()->label('Deduction')->searchable()->preload()->live()->options(function () {
                                $options = Benefit::query()->where('type', 'deduction')->where('company_id', getCompany()->id)->get();
                                $data = [];
                                foreach ($options as $option) {
                                    $data[$option->id] = $option->title . "(" . $option->type . ")";
                                }
                                return $data;
                            })->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                            TextInput::make('amount')->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)->required()->visible(fn($state) => $state > 0)->default(0),
                            TextInput::make('percent')->required()->visible(fn($state) => $state > 0)->default(0),
                        ])->columns(2)->afterStateUpdated(function (Forms\Set $set) {
                            $set('calculation_done', false);
                        })->live(),
                    ])->columns(2)->footerActions([Forms\Components\Actions\Action::make('Calculate')->action(function (Forms\Set $set, Forms\Get $get) {
                        $employee = Employee::query()->firstWhere('id', $get('employee_id'));

// مقدار اولیه
                        $baseAmount = $employee->base_salary ?? 0; // حقوق پایه
                        $grossAmount = $baseAmount; // شروع مقدار Gross با Base Salary

                        $totalAllowance = 0; // مجموع Allowance
                        $totalDeduction = 0; // مجموع Deduction

// تابع محاسبه مزایا و کسورات
                        function calculateBenefit($benefit, $baseAmount, &$grossAmount, $basedOn, $type = 'allowance')
                        {
                            $amount = 0;
                            $benefitData = Benefit::query()->firstWhere('id', $benefit['benefit_id']);
                            if (!$benefitData) {
                                return 0; // در صورتی که مزایا یافت نشود
                            }

                            if ($benefitData->on_change === $basedOn) { // درصد بر اساس مقدار مشخص شده
                                $amount = ($benefit['percent'] > 0)
                                    ? ($basedOn === "base_salary" ? ($baseAmount * $benefit['percent']) / 100 : ($grossAmount * $benefit['percent']) / 100)
                                    : str_replace(',', '', $benefit['amount']);
                            }

                            if ($type === 'allowance') {
                                $grossAmount += $amount; // اضافه به حقوق ناخالص
                            } else {
                                $grossAmount -= $amount; // کسر از حقوق ناخالص
                            }

                            return $amount;
                        }

// ابتدا محاسبه Allowances و Deductions وابسته به base_salary
                        foreach ($get('Allowance') as $benefit) {
                            if (isset($benefit['benefit_id'])) {
                                $totalAllowance += calculateBenefit($benefit, $baseAmount, $grossAmount, 'base_salary', 'allowance');
                            }
                        }

                        foreach ($get('Deduction') as $benefit) {
                            if (isset($benefit['benefit_id'])) {
                                $totalDeduction += calculateBenefit($benefit, $baseAmount, $grossAmount, 'base_salary', 'deduction');
                            }
                        }

// سپس محاسبه Allowances و Deductions وابسته به gross_salary
                        foreach ($get('Allowance') as $benefit) {
                            if (isset($benefit['benefit_id'])) {
                                $totalAllowance += calculateBenefit($benefit, $baseAmount, $grossAmount, 'gross', 'allowance');
                            }
                        }

                        foreach ($get('Deduction') as $benefit) {
                            if (isset($benefit['benefit_id'])) {
                                $totalDeduction += calculateBenefit($benefit, $baseAmount, $grossAmount, 'gross', 'deduction');
                            }
                        }

// تنظیم مقدار نهایی
                        $total = $grossAmount;

// ذخیره مقادیر در متغیرهای خروجی
                        $set('amount_pay', number_format($total));
                        $set('total_allowance', number_format($totalAllowance));
                        $set('total_deduction', number_format($totalDeduction));
                        $set('calculation_done', true);

                    })])->key('sectionID'),

                    Forms\Components\Section::make([

                        Forms\Components\TextInput::make('base')->label('Base Salary')->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)->required()->numeric(),
                        Forms\Components\TextInput::make('total_allowance')->label('Total Allowance')->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)->required()->numeric(),
                        Forms\Components\TextInput::make('total_deduction')->label('Total Deduction')->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)->required()->numeric(),
                        Forms\Components\TextInput::make('amount_pay')->label('Net Pay')->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)->required()->numeric(),

                    ])->columns(4)
                ])->columns(),
                Forms\Components\Hidden::make('calculation_done')->default(false),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('created_at', 'desc')
            ->headerActions([
                Tables\Actions\Action::make('Generate Payroll')->form([
                    Forms\Components\Section::make([
                        Forms\Components\Select::make('employees')->required()->multiple()->options(Employee::query()->where('company_id', getCompany()->id)->pluck('fullName', 'id'))->searchable()->preload(),
                        Forms\Components\Select::make('month')->options([
                            'January',
                            'February',
                            'March',
                            'April',
                            'May',
                            'June',
                            'July',
                            'August',
                            'September',
                            'October',
                            'November',
                            'December'
                        ])->live()->default((now()->month - 1))->searchable()->required(),
                        Forms\Components\Select::make('year')->default(now()->year)->required()->searchable()->options([2024 => 2024, 2025 => 2025, 2026 => 2026, 2027 => 2027, 2028 => 2028, 2029 => 2029, 2030 => 2030]),
                    ])->columns(3)
                ])->action(function ($data) {
                    $employees = Employee::query()->whereIn('id', $data['employees'])->get();
                    $startDate = Carbon::create($data['year'], $data['month'] + 1, 1)->startOfMonth()->toDateString();
                    $endDate = Carbon::create($data['year'], $data['month'] + 1, 1)->endOfMonth()->toDateString();
                    $company = getCompany();
                    $leaveAndOvertime = $company->benefits()->where('built_in', 1)->limit(2)->get();

                    foreach ($employees as $employee) {
                        $baseAmount = $employee->base_salary ?? 0; // حقوق پایه
                        $grossAmount = $baseAmount; // شروع مقدار حقوق ناخالص با حقوق پایه

                        $totalAllowances = 0; // مجموع مزایا
                        $totalDeductions = 0; // مجموع کسورات
                        $benefits = [];
                        $existingPayroll = Payroll::query()
                            ->where('employee_id', $employee->id)
                            ->where('start_date', $startDate)
                            ->where('end_date', $endDate)
                            ->first();

                        if ($existingPayroll) {
                            Notification::make('warning')->warning()->title($employee->fullName." Has Payroll For This Month")->send();
                        }
                        if ($existingPayroll==null){
                            // Allowances
                            foreach ($employee->benefits as $benefit) {
                                if ($benefit->type === "allowance") {
                                    if ($benefit->on_change === "base_salary") {
                                        // درصد بر اساس Base Salary
                                        $amount = ($benefit->percent > 0)
                                            ? ($baseAmount * $benefit->percent) / 100
                                            : $benefit->amount;
                                    } else { // درصد بر اساس Gross Salary
                                        $amount = ($benefit->percent > 0)
                                            ? ($grossAmount * $benefit->percent) / 100
                                            : $benefit->amount;
                                    }

                                    $totalAllowances += $amount; // افزودن به مجموع Allowances
                                    $grossAmount += $amount; // افزودن به حقوق ناخالص

                                    $benefits[] = [
                                        'benefit_id' => $benefit->id,
                                        'company_id' => $company->id,
                                        'amount' => $benefit->percent > 0 ? 0 : $amount, // اگر درصد استفاده شده، مقدار amount برابر null باشد
                                        'percent' => $benefit->percent ?? 0,
                                        'based_on' => $benefit->on_change,
                                    ];
                                }
                            }

// Deductions
                            foreach ($employee->benefits as $benefit) {
                                if ($benefit->type === "deduction") {
                                    if ($benefit->on_change === "base_salary") {
                                        // درصد بر اساس Base Salary
                                        $amount = ($benefit->percent > 0)
                                            ? ($baseAmount * $benefit->percent) / 100
                                            : $benefit->amount;
                                    } else { // درصد بر اساس Gross Salary
                                        $amount = ($benefit->percent > 0)
                                            ? ($grossAmount * $benefit->percent) / 100
                                            : $benefit->amount;
                                    }

                                    $totalDeductions += $amount; // افزودن به مجموع Deductions
                                    $grossAmount -= $amount; // کسر از حقوق ناخالص

                                    $benefits[] = [
                                        'benefit_id' => $benefit->id,
                                        'company_id' => $company->id,
                                        'amount' => $benefit->percent > 0 ? 0 : $amount, // اگر درصد استفاده شده، مقدار amount برابر null باشد
                                        'percent' => $benefit->percent ?? 0,
                                        'based_on' => $benefit->on_change,
                                    ];
                                }
                            }


                            // مرخصی‌ها و اضافه‌کاری
                            $leaves = Leave::query()->where('status', 'accepted')
                                ->where('employee_id', $employee->id)
                                ->whereBetween('start_leave', [$startDate, $endDate])
                                ->whereBetween('end_leave', [$startDate, $endDate])
                                ->whereHas('typeLeave', function ($query) {
                                    return $query->where('is_payroll', 0);
                                })
                                ->get();

                            $totalLeaves = 0;
                            foreach ($leaves as $leave) {
                                $totalLeaves += ($employee->daily_salary * $leave->days);
                            }

                            $overtimes = Overtime::query()->where('employee_id', $employee->id)
                                ->where('status', 'accepted')
                                ->whereBetween('overtime_date', [$startDate, $endDate])
                                ->sum('hours');

                            $hoursPay = $employee->daily_salary / $company->daily_working_hours;
                            $totalOvertime = ($overtimes * $hoursPay) * $company->overtime_rate;

                            // افزودن اضافه‌کاری به مزایا و مرخصی به کسورات
                            $totalAllowances += $totalOvertime;
                            $totalDeductions += $totalLeaves;

                            // محاسبه حقوق نهایی
                            $finalNetPay = ($grossAmount +$totalAllowances)-$totalDeductions;

                            // ایجاد Payroll
                            $payroll = Payroll::query()->create([
                                'amount_pay' => $finalNetPay,
                                'employee_id' => $employee->id,
                                'payment_date' => null,
                                'start_date' => $startDate,
                                'end_date' => $endDate,
                                'status' => 'pending',
                                'user_id' => auth()->id(),
                                'company_id' => $company->id,
                                'total_allowance' => $totalAllowances,
                                'total_deduction' => $totalDeductions,
                            ]);

                            // ثبت اضافه‌کاری و مرخصی در BenefitPayroll
                            if ($totalOvertime > 0) {
                                BenefitPayroll::query()->create([
                                    'company_id' => $company->id,
                                    'amount' => $totalOvertime,
                                    'percent' => 0,
                                    'benefit_id' => $leaveAndOvertime[0]->id,
                                    'payroll_id' => $payroll->id,
                                ]);
                            }
                            if ($totalLeaves > 0) {
                                BenefitPayroll::query()->create([
                                    'company_id' => $company->id,
                                    'amount' => $totalLeaves,
                                    'percent' => 0,
                                    'benefit_id' => $leaveAndOvertime[1]->id,
                                    'payroll_id' => $payroll->id,
                                ]);
                            }

                            // ثبت مزایا و کسورات
                            foreach ($benefits as $benefitData) {
                                BenefitPayroll::query()->create(array_merge($benefitData, ['payroll_id' => $payroll->id]));
                            }
                        }

                    }


                    Notification::make('success')->success()->title('Generate Payroll')->send()->sendToDatabase(auth()->user());
                }),
                Tables\Actions\Action::make('print')->label('Print')->action(function(Table $table){
                    return redirect(route('pdf.payrolls',['ids'=>implode('-',$table->getRecords()->pluck('id')->toArray())]));
                })
            ])
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('employee.fullName')->alignLeft()->numeric()->sortable(),
                Tables\Columns\TextColumn::make('month')->state(fn($record) => Carbon::parse($record->start_date)->format('M'))->alignLeft()->sortable(),
                Tables\Columns\TextColumn::make('year')->state(fn($record) => Carbon::parse($record->start_date)->year)->alignLeft()->sortable(),
                //   Tables\Columns\TextColumn::make('payment_date')->alignCenter()->state(fn($record) => $record->payment_date ? Carbon::make($record->payment_date)->format('Y/m/d') : "Not Paid")->sortable(),
                Tables\Columns\TextColumn::make('employee.base_salary')->label('Base Salary')->alignLeft()->numeric()->sortable(),
                Tables\Columns\TextColumn::make('total_allowance')->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total Allowance'))->label('Total Allowance')->alignLeft()->numeric()->sortable(),
                Tables\Columns\TextColumn::make('total_deduction')->summarize(Tables\Columns\Summarizers\Sum::make('total_deduction')->label('Total Deduction'))->label('Total Deduction')->alignLeft()->numeric()->sortable(),
                Tables\Columns\TextColumn::make('amount_pay')->summarize(Tables\Columns\Summarizers\Sum::make('amount_pay')->label('Total Net Pay'))->label('Net Pay')->alignLeft()->numeric()->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->alignLeft(),
            ])
            ->filters([
                SelectFilter::make('employee_id')->multiple()->searchable()->preload()->options(Employee::where('company_id', getCompany()->id)->get()->pluck('fullName', 'id'))->label('Employee'),
                Filter::make('filter')->form([
                    Forms\Components\Select::make('month')->searchable()->preload()->options([
                        'January',
                        'February',
                        'March',
                        'April',
                        'May',
                        'June',
                        'July',
                        'August',
                        'September',
                        'October',
                        'November',
                        'December'
                    ])->label('Month'),
                    Forms\Components\Select::make('year')->searchable()->preload()->options([2025=>2025,2026=>2026,2027=>2027,2028=>2028,2029=>2029,2030=>2030,2031=>2031])->label('Year')
                ])->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['month'],
                            fn (Builder $query, $date): Builder =>$query->whereDate('start_date',now()->month((int)$data+1)->startOfMonth())->whereDate('end_date',now()->month((int)$data+1)->endOfMonth())   ,
                        )
                        ->when(
                            $data['year'],
                            fn (Builder $query, $date):Builder=> $query->whereDate('start_date',now()->year((int)$date)->startOfMonth())->whereDate('end_date',now()->year((int)$date)->endOfMonth()),
                        );
                })->columns(2)
            ], getModelFilter())
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\ViewAction::make('approve')->iconSize(IconSize::Medium)->color('success')->tooltip(fn($record) => ($record->status->value) === 'Accepted' ? 'Change Status' : 'Approve')->icon(fn($record) => ($record->status->value) === 'Accepted' ? 'heroicon-m-cog-8-tooth' : 'heroicon-o-check-badge')->label(fn($record) => ($record->status->value) === 'Accepted' ? 'Change Status' : 'Approve')->iconSize(IconSize::Medium)->color('success')->form(function ($record) {
                    return [
                        Forms\Components\Section::make([
                            Forms\Components\Select::make('employee_id')->disabled()->default($record->employee_id)->label('Employee')->required()->options(Employee::query()->where('company_id', getCompany()->id)->pluck('fullName', 'id'))->searchable()->preload(),
                            Forms\Components\DatePicker::make('start_date')->required()->live(),
                            Forms\Components\DatePicker::make('end_date')->required()->after(fn(Forms\Get $get) => $get('start_date')),
                            TextInput::make('reference')->hidden()->maxLength(255),
                            Forms\Components\DateTimePicker::make('payment_date')->hidden()->default(now())->required(),
                            Forms\Components\ToggleButtons::make('status')->hidden()->grouped()->options(PayrollStatus::class)->required()->inline(),
                            Forms\Components\Split::make([
                                Forms\Components\Placeholder::make('')->content(function (Forms\Get $get) {
                                    $company = getCompany();
                                    $employee = Employee::query()->firstWhere('id', $get('employee_id'));
                                    if ($employee) {
                                        $leaves = Leave::query()->where('status', 'accepted')->where('employee_id', $employee->id)->whereBetween('start_leave', [$get('start_date'), $get('end_date')])->whereBetween('end_leave', [$get('start_date'), $get('end_date')])->get();
                                        $overtimes = Overtime::query()->where('status', 'accepted')->where('employee_id', $employee->id)->whereBetween('overtime_date', [$get('start_date'), $get('end_date')])->sum('hours');
                                        $hoursPay = $employee->daily_salary / $company->daily_working_hours;
                                        $totalAllowance = number_format($overtimes * $hoursPay * $company->overtime_rate, 2) . $company->currency;
                                        $contentOvertime = "
                                <div style='color: green; display: flex; border: 1px solid whitesmoke; text-align: center; width: 48%;'>
                                    <p style='width: 100%; border: 2px solid black;'>Total Overtime</p>
                                    <p style='width: 100%; border: 2px solid black;'>$overtimes</p>
                                    <p style='width: 100%; border: 2px solid black;'>$totalAllowance</p>
                                </div> ";

                                        $totalDay = 0;
                                        $total = 0;
                                        foreach ($leaves as $leave) {
                                            $totalDay += $leave->days;
                                            $total += ($employee->daily_salary * $leave->days);
                                        }
                                        $total = number_format($total, 2) . $company->currency;
                                        $contentLeave = "
                                <div style='color: red; display: flex   ; border: 1px solid whitesmoke; text-align: center; width: 48%;'>
                                    <p style='width: 100%; border: 2px solid black;'>Total Leave</p>
                                    <p style='width: 100%; border: 2px solid black;'>$totalDay</p>
                                    <p style='width: 100%; border: 2px solid black;'>$total</p>
                                </div>";

                                        $content = "
                                <div style='display: flex; justify-content: space-between; align-items: center;'>
                                    $contentOvertime
                                    $contentLeave
                                </div>";
                                        return new HtmlString($content);
                                    }
                                })
                            ])->columnSpanFull(),
                            Forms\Components\Section::make([
                                Forms\Components\Section::make([
                                    Forms\Components\Repeater::make('Allowance')->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                                        $data['company_id'] = getCompany()->id;
                                        return $data;
                                    })->label('Allowances')->relationship('itemAllowances')->schema([
                                        Forms\Components\Select::make('benefit_id')->required()->label('Allowance')->searchable()->preload()->live()->options(function () {
                                            $options = Benefit::query()->where('type', 'allowance')->where('company_id', getCompany()->id)->get();
                                            $data = [];
                                            foreach ($options as $option) {
                                                $data[$option->id] = $option->title . "(" . $option->type . ")";
                                            }
                                            return $data;
                                        })->afterStateUpdated(function (Forms\Set $set, Forms\Get $get) {
                                            $benefit = Benefit::query()->firstWhere('id', $get('benefit_id'));
                                            if ($benefit) {
                                                if ($benefit->percent > 0) {
                                                    $set("percent", $benefit->percent);
                                                    $set("amount", 0);

                                                } else {
                                                    $set("amount", $benefit->amount);
                                                    $set("percent", 0);
                                                }

                                            } else {
                                                $set("amount", 0);
                                                $set("percent", 0);
                                            }
                                        })->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                                        TextInput::make('amount')->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)->required()->visible(fn($state) => $state > 0)->default(0),
                                        TextInput::make('percent')->required()->visible(fn($state) => $state > 0)->default(0),
                                    ])->columns(2),

                                    Forms\Components\Repeater::make('Deduction')->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                                        $data['company_id'] = getCompany()->id;
                                        return $data;
                                    })->label('Deductions')->relationship('itemDeductions')->schema([
                                        Forms\Components\Select::make('benefit_id')->afterStateUpdated(function (Forms\Set $set, Forms\Get $get) {
                                            $benefit = Benefit::query()->firstWhere('id', $get('benefit_id'));
                                            if ($benefit) {
                                                if ($benefit->percent > 0) {
                                                    $set("percent", $benefit->percent);
                                                    $set("amount", 0);

                                                } else {
                                                    $set("amount", $benefit->amount);
                                                    $set("percent", 0);
                                                }

                                            } else {
                                                $set("amount", 0);
                                                $set("percent", 0);
                                            }
                                        })->required()->label('Deduction')->searchable()->preload()->live()->options(function () {
                                            $options = Benefit::query()->where('type', 'deduction')->where('company_id', getCompany()->id)->get();
                                            $data = [];
                                            foreach ($options as $option) {
                                                $data[$option->id] = $option->title . "(" . $option->type . ")";
                                            }
                                            return $data;
                                        })->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                                        TextInput::make('amount')->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)->required()->visible(fn($state) => $state > 0)->default(0),
                                        TextInput::make('percent')->required()->visible(fn($state) => $state > 0)->default(0),
                                    ])->columns(2),
                                ])->columns(2)->key('sectionID'),
                                Forms\Components\TextInput::make('total_allowance')->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)->required()->numeric(),
                                Forms\Components\TextInput::make('total_deduction')->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)->required()->numeric(),
                                Forms\Components\TextInput::make('amount_pay')->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)->required()->numeric(),
                            ])->columns(3),
                        ])->columns(3),
                    ];
                })->extraModalFooterActions([
                    Tables\Actions\Action::make('Accept')->color('success')->action(function ($record) {
                        $record->update([
                            'status' => 'accepted',
                            'user_id' => auth()->id()
                        ]);
                        return Notification::make('approvePayroll')->title('Approve Payroll ' . $record->employee->fullName)->actions([\Filament\Notifications\Actions\Action::make('Payroll')->color('aColor')])->success()->send()->sendToDatabase(auth()->user());
                    })]
                )->modalWidth(MaxWidth::FitContent)->visible(fn($record) => $record->status->value === "pending"),
                Tables\Actions\Action::make('payment')->visible(fn($record) => $record->status->value === "accepted")->label('Payment')->tooltip('Payment')->icon('heroicon-o-credit-card')->iconSize(IconSize::Medium)->color('warning')->action(function ($data, $record) {
                    $debtor = 0;
                    $creditor = 0;
                    $debtorID = 0;
                    foreach ($data['transactions'] as $transaction) {
                        if ($transaction['creditor'] > 0) {
                            $creditor += str_replace(',', '', $transaction['creditor']);
                            $debtorID = $transaction['account_id'];

                        } else {
                            $debtor += str_replace(',', '', $transaction['debtor']);
                        }
                    }
                    if ($debtor !== $creditor) {
                        Notification::make('warning')->title('Creditor and Debtor not equal')->warning()->send();
                        return;
                    }
                    $period = FinancialPeriod::query()->firstWhere('status', "During");

                    $invoice = Invoice::query()->create([
                        'name' => $data['name'], 'number' => $data['number'], 'date' => $data['date'], 'description' => $data['description'], 'reference' => $data['reference'], 'status' => 'final', 'company_id' => getCompany()->id, 'document' => $data['document']
                    ]);
                    foreach ($data['transactions'] as $transaction) {
                        $transaction['financial_period_id'] = $period->id;
                        $transaction['invoice_id'] = $invoice->id;
                        $transaction['company_id'] = getCompany()->id;
                        $transaction['user_id'] = auth()->id();
                        if ($transaction['debtor'] === null) {
                            $transaction['debtor'] = 0;
                        } elseif ($transaction['creditor'] === null) {
                            $transaction['creditor'] = 0;
                        }
                        Transaction::query()->create($transaction);
                    }
                    $record->update([
                        'payment_date' => $data['date'],
                        'status' => 'payed',
                        'account_id' => $debtorID,
                        'reference' => $data['reference']

                    ]);
                    return Notification::make('Create Invoice Payroll')->success()->title('Pay Payroll')->actions([\Filament\Notifications\Actions\Action::make('Payroll')])->send()->sendToDatabase(auth()->user());
                })->form(function ($record) {
                    return [
                        Forms\Components\Section::make([
                            Forms\Components\TextInput::make('name')->default($record->employee->fullName . " " . Carbon::make($record->start_date)->format('Y/m/d') . " - " . Carbon::make($record->end_date)->format('Y/m/d') . " Payroll")->required()->maxLength(255),
                            Forms\Components\TextInput::make('number')->numeric()->required()->maxLength(255)->readOnly()->default(getCompany()->financialPeriods()->where('status', "During")->first()->invoices()->get()->last()?->number != null ? getCompany()->financialPeriods()->where('status', "During")->first()->invoices()->get()->last()->number + 1 : 1),
                            Forms\Components\DateTimePicker::make('date')->required()->default(now()),
                            Forms\Components\TextInput::make('reference')->maxLength(255),
                            Forms\Components\FileUpload::make('document')->nullable()->columnSpanFull(),
                            Forms\Components\Textarea::make('description')->nullable()->columnSpanFull(),
                            Forms\Components\Section::make([
                                Forms\Components\Repeater::make('transactions')->label('Accounting Journal')->schema([
                                    SelectTree::make('account_id')->model(Transaction::class)->live()->defaultOpenLevel(2)->label('Subsidiary Account / Detail Account')->required()->relationship('account', 'name', 'parent_id', modifyQueryUsing: fn($query) => $query->where('level', '!=', 'control')->where('company_id', getCompany()->id)),
                                    Forms\Components\Textarea::make('description')->required(),
                                    Forms\Components\TextInput::make('debtor')->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->live()->default(0)->minValue(0)  ->rules([
                                        fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                            if ( $get('debtor') == 0 && $get('creditor') == 0) {
                                                $fail('Only one of these values can be zero.');
                                            }elseif($get('debtor') != 0 && $get('creditor') != 0){
                                                $fail('At least one of the values must be zero.');
                                            }
                                        },
                                    ]),
                                    Forms\Components\TextInput::make('creditor')  ->rules([
                                        fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                            if ( $get('debtor') == 0 && $get('creditor') == 0) {
                                                $fail('Only one of these values can be zero.');
                                            }elseif($get('debtor') != 0 && $get('creditor') != 0){
                                                $fail('At least one of the values must be zero.');
                                            }
                                        },
                                    ])->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->live()->default(0)->minValue(0),
                                ])->minItems(2)->maxItems(2)->columns()->defaultItems(2)
                                    ->mutateRelationshipDataBeforecreateUsing(function (array $data): array {
                                        $data['user_id'] = auth()->id();
                                        $data['company_id'] = getCompany()->id;
                                        return $data;
                                    })
                            ])
                        ])->columns(2)
                    ];
                })->modalSubmitActionLabel('Payment')->modalWidth(MaxWidth::SixExtraLarge),
                Tables\Actions\Action::make('pdf')->tooltip('Print')->icon('heroicon-s-printer')->iconSize(IconSize::Medium)->label('')
                    ->url(fn($record) => route('pdf.payroll', ['id' => $record->id]))->openUrlInNewTab(),
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

    public static function getNavigationBadge(): ?string
    {

        return self::$model::query()->where('status', 'pending')->where('company_id', getCompany()->id)->count();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayrolls::route('/'),
            'create' => Pages\CreatePayroll::route('/create'),
            'edit' => Pages\EditPayroll::route('/{record}/edit'),
        ];
    }
}
