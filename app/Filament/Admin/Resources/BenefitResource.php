<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\BenefitResource\Pages;
use App\Filament\Admin\Resources\BenefitResource\RelationManagers;
use App\Filament\Clusters\HrSettings;
use App\Models\Benefit;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use PhpParser\Node\Expr\Ternary;

class BenefitResource extends Resource
{

    protected static ?string $model = Benefit::class;
    protected static ?string $cluster = HrSettings::class;
    protected static ?string $label='Allowance/Deduction';
     protected static ?string $pluralLabel='Allowance/Deduction';
    protected static ?string $navigationIcon = 'heroicon-c-squares-plus';
    protected static ?string $navigationGroup = 'HR Management System';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')->required()->maxLength(255),
                Forms\Components\ToggleButtons::make('price_type')->afterStateUpdated(function (Forms\Set $set){
                    $set('amount',0);
                    $set('percent',0);
                })->inline()->grouped()->options(['0'=>'Price $','1'=>'Percent %'])->default(0)->live(),
                TextInput::make('percent')->columnSpanFull()->maxValue(100)->minValue(1)->suffix('%')->visible(fn(Get $get)=>$get('price_type'))->requiredIf('price_type','1'),
                Forms\Components\TextInput::make('amount')->columnSpanFull()->hidden(fn(Get $get)=>$get('price_type'))->suffixIcon('cash')->suffixIconColor('success')->minValue(1)->requiredIf('price_type','0')->numeric()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
                Forms\Components\ToggleButtons::make('type')->inline()->grouped()->required()->options(['allowance'=>'Allowance','deduction'=>'Deduction']),
                Forms\Components\ToggleButtons::make('on_change')->inline()->grouped()->label('Effect on')->required()->options(['base_salary'=>'Base Salary','gross'=>' Gross']),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('title')->alignCenter()->sortable(),
                Tables\Columns\TextColumn::make('amount')->alignCenter()->numeric()->sortable(),
                Tables\Columns\TextColumn::make('percent')->alignCenter()->prefix('%')->sortable(),
                Tables\Columns\TextColumn::make('type')->state(fn($record)=>str($record->type)->title())->alignCenter()->sortable(),
                Tables\Columns\TextColumn::make('on_change')->state(fn($record)=> $record->on_change === "base_salary" ? "Base Salary" : "Gross")->label('Effect on')->alignCenter()->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')->options(['allowance'=>'allowance','deduction'=>'deduction'])->searchable()->preload(),
                SelectFilter::make('on_change')->label('Effect on')->options(['base_salary'=>'base_salary','allowance/deduction'=>'allowance/deduction'])->searchable()->preload(),
                Filter::make('amount')
                    ->form([
                        TextInput::make('min')->label('Min Amount')
                            ->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)
                            ->numeric(),

                        TextInput::make('max')->label('Max Amount')
                        ->mask(RawJs::make('$money($input)'))->stripCharacters(',')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)
                            ->numeric(),
                    ])->columnSpanFull()
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min'],
                                fn(Builder $query, $date): Builder => $query->where('amount', '>=', str_replace(',','',$date)),
                            )
                            ->when(
                                $data['max'],
                                fn(Builder $query, $date): Builder => $query->where('amount', '<=', str_replace(',','',$date)),
                            );
                    }),
            ], getModelFilter())
            ->actions([
                Tables\Actions\EditAction::make()->modelLabel('Edit'),
                Tables\Actions\DeleteAction::make('delete')->hidden(fn($record)=>(bool)$record->built_in)
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
            'index' => Pages\ListBenefits::route('/'),
//            'create' => Pages\CreateBenefit::route('/create'),
//            'edit' => Pages\EditBenefit::route('/{record}/edit'),
        ];
    }
}
