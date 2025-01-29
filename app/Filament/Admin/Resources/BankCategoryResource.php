<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\BankCategoryResource\Pages;
use App\Filament\Admin\Resources\BankCategoryResource\RelationManagers;
use App\Models\Bank_category;
use App\Models\BankCategory;
use App\Models\VendorType;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BankCategoryResource extends Resource
{

    protected static ?string $model = Bank_category::class;
    protected static ?string $label = 'Incomes/Expenses Type';
    protected static ?string $navigationGroup = 'Finance Management';
    protected static ?string $cluster = \App\Filament\Clusters\AccountSettings::class;

    protected static ?int $navigationSort=10;
    protected static ?string $navigationIcon = 'heroicon-m-wallet';
    public static function canAccess(): bool
    {
        return false;
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')->required()->maxLength(255),
                Forms\Components\ToggleButtons::make('type')->afterStateUpdated(fn(Forms\Set $set)=> $set('parent_id',null)) ->grouped()->options([0=>'Expense',1=>'Income'])->colors(['danger','success'])->inline(),
                Forms\Components\Select::make('parent_id')->columnSpanFull()->label('Parent')->searchable()->preload()->options(fn(Forms\Get $get)=>Bank_category::query()->where('type',$get('type'))->where('company_id',getCompany()->id)->pluck('title','id')),
                Forms\Components\Textarea::make('description')->columnSpanFull()->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('title')->searchable(),
                Tables\Columns\TextColumn::make('type')->color(fn($record)=>$record->type ?  "success":'danger')->state(fn($record)=>$record->type ?  "Income":'Expense')->badge()->searchable(),
                Tables\Columns\TextColumn::make('children.title')->badge()->alignCenter(),
                Tables\Columns\TextColumn::make('total_amount')->state(fn($record)=>$record->type ?   number_format($record->incomes()->sum('amount'),2) : number_format($record->expenses()->sum('amount'),2))->label('Total Amount'),
             
            ])
            ->filters([
                //
            ])
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBankCategories::route('/'),
//            'create' => Pages\CreateBankCategory::route('/create'),
//            'edit' => Pages\EditBankCategory::route('/{record}/edit'),
        ];
    }
}
