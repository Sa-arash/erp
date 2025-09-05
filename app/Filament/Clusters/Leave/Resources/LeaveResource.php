<?php

namespace App\Filament\Clusters\Leave\Resources;

use App\Enums\LeaveStatus;
use App\Filament\Clusters\Leave;
use App\Filament\Clusters\Leave\Resources\LeaveResource\Pages;
use App\Filament\Clusters\Leave\Resources\LeaveResource\RelationManagers;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\Leave as ModelLeave;
use App\Models\Overtime;
use App\Models\Typeleave;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\ActionSize;
use Filament\Support\Enums\IconSize;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class LeaveResource extends Resource implements HasShieldPermissions

{
    protected static ?string $model = ModelLeave::class;
    protected static ?string $navigationGroup = 'HR Management System';
    protected static ?int $navigationSort=3;
    protected static ?string $pluralLabel="Leave ";
     protected static ?string $label="Leave";
    protected static ?string $navigationIcon = 'heroicon-o-folder-minus';
    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'admin'
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
              Forms\Components\Section::make([
                  Forms\Components\Select::make('employee_id')->label('Employee')->required()->options(Employee::query()->where('company_id', getCompany()->id)->pluck('fullName', 'id'))->searchable()->preload(),

                  Forms\Components\Select::make('typeleave_id')
                      ->createOptionForm([
                          Forms\Components\TextInput::make('title')->maxLength(250)->required(),
                          Forms\Components\TextInput::make('days')->label('Max Days')->numeric()->required(),
                          Forms\Components\ToggleButtons::make('is_payroll')->inline()->boolean('Paid Leave', 'Unpaid Leave')->label('Payment')->required(),
                          Forms\Components\Textarea::make('description')->nullable()->maxLength(255)->columnSpanFull()
                      ])
                      ->createOptionUsing(function (array $data): int {
                          return Typeleave::query()->create([
                              'title' => $data['title'],
                              'days' => $data['days'],
                              'is_payroll' => $data['is_payroll'],
                              'description' => $data['description'],
                              'company_id' => getCompany()->id
                          ])->getKey();
                      })->label('Leave Type')->required()->options(Typeleave::query()->where('company_id', getCompany()->id)->pluck('title', 'id'))->searchable()->preload(),
                  ToggleButtons::make('type')->required()->grouped()->boolean('R&R','Home'),

              ])->columns(3),
                Forms\Components\DatePicker::make('start_leave')->default(now())->live()->afterStateUpdated(function ( Forms\Get $get ,Forms\Set $set){
                    $start = Carbon::parse($get('start_leave'));

                    $end = Carbon::parse($get('end_leave'));
                    $period = CarbonPeriod::create($start, $end);
                    $daysBetween = $period->count(); // تعداد کل روزها
                    $CompanyHoliday = count(getDaysBetweenDates($start, $end, getCompany()->weekend_days));

                    $holidays = Holiday::query()->where('company_id', getCompany()->id)->whereBetween('date', [$start, $end])->count();
                    $validDays = $daysBetween - $holidays-$CompanyHoliday;
                    $set('days', $validDays);
                })->required()->default(now()),
                Forms\Components\DatePicker::make('end_leave')->default(now())->afterStateUpdated(function ( Forms\Get $get ,Forms\Set $set){
                    $start = Carbon::parse($get('start_leave'));

                    $end = Carbon::parse($get('end_leave'));
                    $period = CarbonPeriod::create($start, $end);
                    $daysBetween = $period->count(); // تعداد کل روزها
                    $CompanyHoliday = count(getDaysBetweenDates($start, $end, getCompany()->weekend_days));

                    $holidays = Holiday::query()->where('company_id', getCompany()->id)->whereBetween('date', [$start, $end])->count();
                    $validDays = $daysBetween - $holidays-$CompanyHoliday;
                    $set('days', $validDays);
                })->live()->required(),
                Forms\Components\TextInput::make('days')->columnSpanFull()->required()->numeric(),
                ToggleButtons::make('is_circumstances')->live()->default(0)->required()->boolean('Yes','No')->grouped()->label('Aware of any Circumstances'),
                Textarea::make('explain_leave')->required(fn(Get $get)=>$get('is_circumstances'))->label('Explain'),
              Forms\Components\Section::make([
                  Forms\Components\FileUpload::make('document')->downloadable(),
                  Forms\Components\Textarea::make('description'),
              ])->columns()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->searchable()->defaultSort('id','desc')
            ->columns([
                Tables\Columns\TextColumn::make(getRowIndexName())->rowIndex(),
                Tables\Columns\TextColumn::make('employee.fullName')->alignLeft()->sortable(),
                Tables\Columns\TextColumn::make('typeLeave.title')->alignLeft()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Request Date')->date()->alignLeft()->sortable(),
                Tables\Columns\TextColumn::make('approvals.employee.fullName')->label('Line Manager')->sortable(),
                Tables\Columns\TextColumn::make('approval_date')->label('Approval Date')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('start_leave')->label('Start Leave')->date()->sortable(),
                Tables\Columns\TextColumn::make('end_leave')->label('End Leave')->date()->sortable(),
                Tables\Columns\TextColumn::make('days')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('status')->badge(),
            ])
            ->filters([
                SelectFilter::make('status')->label('Leave status')->searchable()->preload()->options(LeaveStatus::class),
                SelectFilter::make('employee_id')->searchable()->preload()->options(Employee::where('company_id', getCompany()->id)->get()->pluck('fullName', 'id'))->label('Employee'),
                SelectFilter::make('typeLeave_id')->searchable()->preload()->options(Typeleave::where('company_id', getCompany()->id)->get()->pluck('title', 'id'))->label(' Leave Type '),
                SelectFilter::make('user_id')->searchable()->preload()->options(getCompany()->users->pluck('name', 'id'))->label('Approver'),
                DateRangeFilter::make('created_at'),
                DateRangeFilter::make('approval_date'),
                DateRangeFilter::make('start_leave'),
                DateRangeFilter::make('end_leave'),
                Filter::make('days')
                    ->form([
                        TextInput::make('min')->label('Min Days')
                            ->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)
                            ->numeric(),

                        TextInput::make('max')->label('Max Days')
                            ->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)
                            ->numeric(),
                    ])->columnSpanFull()
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min'],
                                fn(Builder $query, $date): Builder => $query->where('days', '>=', str_replace(',', '', $date)),
                            )
                            ->when(
                                $data['max'],
                                fn(Builder $query, $date): Builder => $query->where('days', '<=', str_replace(',', '', $date)),
                            );
                    }),


            ], getModelFilter())
            ->actions([
                Tables\Actions\Action::make('pdf')->size(ActionSize::Medium)->tooltip('Print')->icon('heroicon-s-printer')->label('PDF')
                    ->url(fn($record) => route('pdf.leaverequest', ['id' => $record->id]))->openUrlInNewTab(),
                Tables\Actions\EditAction::make()->hidden(fn($record)=>$record->status->name!=="Pending"),
                Tables\Actions\Action::make('approve')->iconSize(IconSize::Medium)->color('success')
                ->icon(fn($record)=>($record->status->value) === 'accepted'?'heroicon-m-cog-8-tooth':'heroicon-o-check-badge')->label(fn($record)=>($record->status->value) === 'accepted'?'Change Status':'Approve')
                ->form(function ($record) {
                    return [
                        Forms\Components\Section::make([
                            Forms\Components\Select::make('employee_id')->disabled()->default($record->employee_id)->label('Employee')->required()->options(Employee::query()->where('company_id', getCompany()->id)->pluck('fullName', 'id'))->searchable()->preload(),
                            Forms\Components\Select::make('typeleave_id')->columnSpan(2)->disabled()->default($record->typeleave_id)->label('Leave Type')->required()->options(Typeleave::query()->where('company_id', getCompany()->id)->pluck('title', 'id'))->searchable()->preload(),
                            Forms\Components\DatePicker::make('start_leave')->disabled()->default($record->start_leave)->required(),
                            Forms\Components\DatePicker::make('end_leave')->disabled()->default($record->end_leave)->required(),
                            Forms\Components\TextInput::make('days')->disabled()->required()->numeric()->default($record->days),
                            ToggleButtons::make('is_circumstances')->disabled()->live()->default(0)->required()->boolean('Yes','No')->grouped()->label('Aware of any Circumstances'),
                            Textarea::make('explain_leave')->disabled()->columnSpan(2)->required(fn(Get $get)=>$get('is_circumstances'))->label('Explain'),
                            Forms\Components\FileUpload::make('document')->downloadable()->columnSpan(2)->default($record->document)->disabled(),
                            Forms\Components\Textarea::make('description')->default($record->description)->disabled(),
                        ])->columns(3),
                        Forms\Components\Section::make([
                            Forms\Components\Placeholder::make('Total Leave('.now()->format('Y').")")->content(function ()use($record){
                                $leaves= ModelLeave::query()->where('employee_id',$record->employee_id)->whereBetween('start_leave', [now()->startOfYear(), now()->endOfYear()])->whereBetween('end_leave', [now()->startOfYear(), now()->endOfYear()])->where('status','accepted')->sum('days');
                                return new HtmlString("<div style='font-size: 25px !important;'>  <span style='color: red;font-size: 25px !important;'>$leaves</span> Days </div>");
                            }),
                            Forms\Components\ToggleButtons::make('status')->grouped()->default($record->status)->options(LeaveStatus::class)->inline()->required(),
                            Forms\Components\Textarea::make('comment')->columnSpanFull(),
                        ])
                    ];
                }
                )->visible(fn($record)=>$record->admin_id ===null and auth()->user()->can('admin_leave') and $record->status->value=="approveHead")->action(function ($data,$record) {

                    $record->update([
                        'comment'=>$data['comment'],
                        'status'=>$data['status']->value,
                        'approval_date'=>now(),
                        'user_id'=>auth()->id(),
                        'admin_id'=>getEmployee()->id
                    ]);
                        Notification::make('approveLeave')->title('Approved Leave Employee:'.$record->employee->fullName)->success()->send()->sendToDatabase(auth()->user(),true);
                    })
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

    public static function getNavigationBadge(): ?string
    {

        return self::$model::query()->where('status', 'approveHead')->where('company_id', getCompany()->id)->count();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeaves::route('/'),
            'create' => Pages\CreateLeave::route('/create'),
            'edit' => Pages\EditLeave::route('/{record}/edit'),
        ];
    }
}
