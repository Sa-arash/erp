<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeaveResource\Pages;
use App\Enums\LeaveStatus;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\Typeleave;
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

class LeaveResource extends Resource
{
    protected static ?string $model = Leave::class;
    protected static ?string $navigationGroup = 'Human Resource';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('employee_id')->label('Employee')->required()->options(Employee::query()->pluck('fullName','id'))->searchable()->preload(),
                Forms\Components\Select::make('typeleave_id')->label('Leave Type')->required()->options(Typeleave::query()->pluck('title','id'))->searchable()->preload(),
                Forms\Components\DatePicker::make('start_leave')
                    ->required(),
                Forms\Components\DatePicker::make('end_leave')
                    ->required(),
                Forms\Components\TextInput::make('days')
                    ->required()
                    ->numeric(),
                Forms\Components\ToggleButtons::make('status')->options(LeaveStatus::class)->inline()
                    ->required(),
                Forms\Components\FileUpload::make('document')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),

                Forms\Components\Hidden::make('user_id')->default(auth()->id())
                    ->required(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->searchable()
            ->columns([
                Tables\Columns\TextColumn::make('employee.fullName')->alignCenter()->sortable(),
                Tables\Columns\TextColumn::make('typeLeave.title')->alignCenter()->sortable(),
                Tables\Columns\TextColumn::make('user.name')->label('User Request')->sortable()->alignCenter(),
                Tables\Columns\TextColumn::make('created_at')->label('Request Date')->dateTime()->alignCenter()->sortable(),
                Tables\Columns\TextColumn::make('start_leave')->date()->sortable(),
                Tables\Columns\TextColumn::make('end_leave')->date()->sortable(),
                Tables\Columns\TextColumn::make('days')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('status'),
            ])
            ->filters([
                SelectFilter::make('status')->label('Leave status')->searchable()->preload()->options(LeaveStatus::class),

                SelectFilter::make('employee_id')->searchable()->preload()->options(Employee::all()->pluck('fullName', 'id'))
                    ->label('Employee'),

                SelectFilter::make('typeLeave_id')->searchable()->preload()->options(Typeleave::all()->pluck('title', 'id'))
                    ->label('Type Leave'),

                SelectFilter::make('user_id')->searchable()->preload()->options(User::all()->pluck('name', 'id'))
                    ->label('User'),




                DateRangeFilter::make('created_at'),
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
                                fn(Builder $query, $date): Builder => $query->where('days', '>=', str_replace(',','',$date)),
                            )
                            ->when(
                                $data['max'],
                                fn(Builder $query, $date): Builder => $query->where('days', '<=', str_replace(',','',$date)),
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

        return self::$model::query()->where('status','waiting')->count();
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
