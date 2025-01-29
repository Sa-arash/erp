<?php

namespace App\Filament\Resources;

use App\Enums\PayrollStatus;
use App\Filament\Resources\PayrollResource\Pages;
use App\Filament\Resources\PayrollResource\RelationManagers;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class PayrollResource extends Resource
{
    protected static ?string $model = Payroll::class;
    protected static ?string $navigationGroup = 'HR Management System';
    protected static ?string $navigationIcon = 'payment';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('employee_id')->suffixIcon('employee')->suffixIconColor('primary')->label('Employee')->searchable()->preload()->options(Employee::all()->pluck('fullName', 'id'))->required(),
                Forms\Components\TextInput::make('amount_pay')->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)
                    ->required()
                    ->numeric(),
                Forms\Components\DateTimePicker::make('payment_date')->default(now())->required(),
                Forms\Components\ToggleButtons::make('status')->options(PayrollStatus::class)
                    ->required()->inline(),
                Forms\Components\Hidden::make('user_id')->default(auth()->id())
                    ->required(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),

                Tables\Columns\TextColumn::make('employee.fullName')->alignCenter()
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount_pay')->alignCenter()
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')->tooltip(fn($record)=>$record->payment_date ?$record->payment_date:"Not Paid" )->alignCenter(),
                Tables\Columns\TextColumn::make('user.name')->alignCenter()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')->alignCenter()
                    ->dateTime()
                    ->sortable()
            ])
            ->filters([
                SelectFilter::make('Status')->options(PayrollStatus::class)->searchable()->preload(),

                SelectFilter::make('employee_id')->searchable()->preload()->options(Employee::all()->pluck('fullName', 'id'))
                    ->label('Employee'),
                SelectFilter::make('user_id')->searchable()->preload()->options(User::all()->pluck('name', 'id'))
                    ->label('User'),
                DateRangeFilter::make('payment_date'),
                DateRangeFilter::make('created_at'),
                Filter::make('amount_pay')
                    ->form([
                        TextInput::make('min')->label('Min Amount Pay')
                            ->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)
                            ->numeric(),

                        TextInput::make('max')->label('Max Amount Pay')
                        ->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)
                            ->numeric(),
                    ])->columnSpanFull()
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min'],
                                fn(Builder $query, $date): Builder => $query->where('amount_pay', '>=', str_replace(',','',$date)),
                            )
                            ->when(
                                $data['max'],
                                fn(Builder $query, $date): Builder => $query->where('amount_pay', '<=', str_replace(',','',$date)),
                            );
                    }),



            ], getModelFilter())

            ->actions([
                Tables\Actions\EditAction::make(),
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

        return self::$model::query()->count();
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
