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
                Tables\Actions\ViewAction::make()->infolist([
                    Section::make([
                        TextEntry::make('employee.info'),
                        TextEntry::make('created_at')->date()->label('Date of Resignation Submission'),
                        TextEntry::make('date')->date()->label('Date of Resignation '),
                        TextEntry::make('reason')->columnSpanFull()->label('Reason for Resignation:'),
                        TextEntry::make('feedback')->columnSpanFull(),
                        RepeatableEntry::make('approvals')->schema([
                            TextEntry::make('employee.info'),
                            TextEntry::make('position'),
                            TextEntry::make('status'),
                            TextEntry::make('approve_date')->date(),
                            TextEntry::make('comment')
                        ])->columnSpanFull()->columns(4)
                    ])->columns(3)
                ]),
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
