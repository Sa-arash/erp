<?php

namespace App\Filament\Admin\Resources\EmployeeResource\RelationManagers;

use App\Enums\LoanStatus;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\Typeleave;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LeavesRelationManager extends RelationManager
{
    protected static string $relationship = 'leaves';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('employee.fullName')->alignCenter()->sortable(),
                Tables\Columns\TextColumn::make('typeLeave.title')->alignCenter()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Request Date')->date()->alignCenter()->sortable(),
                Tables\Columns\TextColumn::make('approval_date')->tooltip(fn($record) => $record->user?->name)->date()->sortable(),
                Tables\Columns\TextColumn::make('start_leave')->date()->sortable(),
                Tables\Columns\TextColumn::make('end_leave')->date()->sortable(),
                Tables\Columns\TextColumn::make('days')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('status')->badge(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('Request Leave')
                    ->form([

                        Forms\Components\Select::make('typeleave_id')
                            // ->createOptionForm([
                            //     Forms\Components\TextInput::make('title')->maxLength(250)->required(),
                            //     Forms\Components\TextInput::make('days')->label('Max Days')->numeric()->required(),
                            //     Forms\Components\ToggleButtons::make('is_payroll')->inline()->boolean('Paid Leave', 'Unpaid Leave')->label('Payment')->required(),
                            //     Forms\Components\Textarea::make('description')->nullable()->maxLength(255)->columnSpanFull()
                            // ])
                            // ->createOptionUsing(function (array $data): int {
                            //     return Typeleave::query()->create([
                            //         'title' => $data['title'],
                            //         'days' => $data['days'],
                            //         'is_payroll' => $data['is_payroll'],
                            //         'description' => $data['description'],
                            //         'company_id' => getCompany()->id
                            //     ])->getKey();
                            // })
                            ->label('Leave Type')->required()->options(Typeleave::query()->where('company_id', getCompany()->id)->pluck('title', 'id'))->searchable()->preload(),
                        Forms\Components\DatePicker::make('start_leave')->required(),
                        Forms\Components\DatePicker::make('end_leave')->required(),
                        Forms\Components\TextInput::make('days')->hintAction(Forms\Components\Actions\Action::make('calculate')->action(function (Forms\Get $get, Forms\Set $set) {
                            $start = Carbon::parse($get('start_leave'));
                            $end = Carbon::parse($get('end_leave'));
                            $period = CarbonPeriod::create($start, $end);
                            $daysBetween = $period->count(); // تعداد کل روزها
                            $holidays = Holiday::query()->where('company_id', getCompany()->id)->whereBetween('date', [$start, $end])->count();
                            $validDays = $daysBetween - $holidays;
                            $set('days', $validDays);
                        }))->required()->numeric(),
                        Forms\Components\Section::make([
                            Forms\Components\FileUpload::make('document')->downloadable(),
                            Forms\Components\Textarea::make('description'),
                        ])->columns()
                    ])
                    ->action(function (array $data,  $record): void {
                        $data['company_id'] = getCompany()->id;
                        $data['employee_id'] = Employee::query()->firstWhere('user_id', auth()->user()->id)->id;
                        Leave::query()->create($data);
                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
