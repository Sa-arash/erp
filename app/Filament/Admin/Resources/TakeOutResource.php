<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TakeOutResource\Pages;
use App\Filament\Admin\Resources\TakeOutResource\RelationManagers;
use App\Models\Employee;
use App\Models\TakeOut;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TakeOutResource extends Resource
{
    protected static ?string $model = TakeOut::class;

    protected static ?string $navigationIcon = 'heroicon-c-arrow-up-tray';



    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('employee.fullName'),
                Tables\Columns\TextColumn::make('assets.product.title')->state(fn($record)=> $record->assets->pluck('title')->toArray())->badge()->label('Assets'),
                Tables\Columns\TextColumn::make('from'),
                Tables\Columns\TextColumn::make('to'),
                Tables\Columns\TextColumn::make('date')->date(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('type')->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('employee_id')->options(Employee::query()->where('company_id',getCompany()->id)->pluck('fullName','id'))
            ])
            ->actions([
                Tables\Actions\ViewAction::make('view')->infolist([
                    Section::make([
                        TextEntry::make('employee.fullName'),
                        TextEntry::make('from'),
                        TextEntry::make('to'),
                        TextEntry::make('date')->date(),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('type')->badge(),
                        RepeatableEntry::make('items')->label('Assets')->schema([
                            TextEntry::make('asset.title'),
                            TextEntry::make('remarks'),
                            TextEntry::make('returned_date'),
                        ])->columnSpanFull()->columns(3)
                    ])->columns()
                ])
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
            'index' => Pages\ListTakeOuts::route('/'),
        ];
    }
}
