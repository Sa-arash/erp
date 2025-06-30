<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SeparationResource\Pages;
use App\Filament\Admin\Resources\SeparationResource\RelationManagers;
use App\Models\Separation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconSize;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SeparationResource extends Resource
{
    protected static ?string $model = Separation::class;
    protected static ?string $label = "Clearance";
    protected static ?string $navigationGroup = 'HR Management System';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationIcon = 'heroicon-m-arrow-left-on-rectangle';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('employee_id')->searchable()->preload()->relationship('employee', 'fullName', modifyQueryUsing: fn($query) => $query->where('company_id', getCompany()->id))->required(),
                Forms\Components\DatePicker::make('date')->after(now())->default(now())->label('Date of Resignation ')->required(),
                Forms\Components\Textarea::make('reason')->label('Reason for Resignation')->required()->columnSpanFull(),
                Forms\Components\Textarea::make('feedback')->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('employee.info')->sortable(),
                Tables\Columns\TextColumn::make('reason')->label('Reason for Resignation')->limit(30),
                Tables\Columns\TextColumn::make('feedback')->limit(30),
                Tables\Columns\TextColumn::make('date')->label('Date of Resignation ')->date()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Date of Resignation Submission')->date()->sortable(),

            ])
            ->filters([
                //
            ])
            ->actions([
               Tables\Actions\Action::make('pdf')->tooltip('Print Preview')->icon('heroicon-s-printer')->iconSize(IconSize::Medium)->label('')->url(fn($record)=>route('pdf.clearance',['id'=>$record->id,'company'=>$record->company_id]))
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
            'index' => Pages\ListSeparations::route('/'),
            'create' => Pages\CreateSeparation::route('/create'),
            'edit' => Pages\EditSeparation::route('/{record}/edit'),
        ];
    }
}
