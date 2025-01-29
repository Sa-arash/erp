<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Company;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerResource extends Resource
{
    protected static ?int $navigationSort = 2;
    protected static ?string $model = Customer::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';
    protected static ?string $navigationGroup = 'Finance Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('img')->label('Profile Picture')->image()->columnSpanFull()->imageEditor()->extraAttributes(['style'=>'width:150px!important;border-radius:10px !important']),
                Forms\Components\TextInput::make('name')->required()->maxLength(255),
                Forms\Components\TextInput::make('NIC')->unique('vendors','NIC',ignoreRecord: true)->label('NIC')->nullable()->maxLength(255),
                Forms\Components\Select::make('company_id')->columnSpanFull()->label('Company')->searchable()->preload()->options(Company::query()->pluck('title','id'))->required(),
                Forms\Components\TextInput::make('phone')->tel()->numeric()->nullable()->maxLength(255),
                Forms\Components\TextInput::make('website')->nullable()->suffixIcon('heroicon-c-globe-americas')->maxLength(255),
                Forms\Components\TextInput::make('email')->unique('vendors','email',ignoreRecord: true)->email()->nullable()->maxLength(255),
                Forms\Components\ToggleButtons::make('gender')->options(['male'=>'male','female'=>'female','other'=>'other'])->required()->inline()->grouped(),
                Forms\Components\Select::make('country')->nullable()->options(getCountry())->searchable()->preload(),
                Forms\Components\TextInput::make('state')->label('State/Province')->nullable()->maxLength(255),
                Forms\Components\TextInput::make('city')->nullable()->maxLength(255),
                Forms\Components\Textarea::make('description')->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('phone')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('amount')->state(fn($record)=> $record->incomes->sum('amount'))->sortable(query: function (Builder $query, string $direction): Builder {return $query->withSum('incomes','amount')->orderBy('incomes_sum_amount',$direction);})->badge()->numeric(),
                Tables\Columns\TextColumn::make('total_amount')->label('Balance')->sortable()->badge()->numeric(),
                Tables\Columns\TextColumn::make('company.title')->searchable(),

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
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
