<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Filament\Resources\ExpenseResource\RelationManagers;
use App\Models\Bank_category;
use App\Models\Company;
use App\Models\Expense;
use App\Models\Vendor;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static ?string $navigationGroup = 'Finance Management';
    protected static ?string $navigationIcon = 'heroicon-m-document-minus';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
                Forms\Components\DatePicker::make('date')->required(),
                Forms\Components\TextInput::make('amount')->suffixIcon('cash')->suffixIconColor('success')->minValue(0)->required()->mask(RawJs::make('$money($input)'))->stripCharacters(',')->maxLength(255),
                Forms\Components\TextInput::make('reference')->label('Factor Number')->maxLength(255)->nullable()->columnSpanFull(),
                Select::make('vendor_id')->label('Vendor')->options(Vendor::query()->pluck('name', 'id'))->searchable()->preload()->required(),
                Select::make('category_id')->label('Category')->options(Bank_category::query()->where('type', 0)->pluck('title', 'id'))->searchable()->preload()->required()->createOptionForm([
                    Forms\Components\TextInput::make('title')->required()->maxLength(255),
                    Forms\Components\Textarea::make('description')->columnSpanFull()->maxLength(255),
                    Forms\Components\Select::make('company_id')->columnSpanFull()->label('Company')->searchable()->preload()->options(Company::query()->pluck('title','id'))->required(),

                ])->createOptionUsing(function (array $data): int {
                    $data['type'] = 0;
                    return Bank_category::query()->create($data)->getKey();
                }),
                Forms\Components\Select::make('company_id')->columnSpanFull()->label('Company')->searchable()->preload()->options(Company::query()->pluck('title','id'))->required(),

                Forms\Components\Textarea::make('description')->columnSpanFull(),
                Forms\Components\FileUpload::make('payment_receipt_image')->columnSpanFull()->image()->imageEditor(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
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
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
