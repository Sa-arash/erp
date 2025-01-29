<?php

namespace App\Filament\Resources;

use App\Filament\Admin\Resources\VendorTypeResource;
use App\Filament\Resources\VendorResource\Pages;
use App\Filament\Resources\VendorResource\RelationManagers;
use App\Models\Company;
use App\Models\Vendor;
use App\Models\VendorType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VendorResource extends Resource
{
    protected static ?int $navigationSort = 3;
    protected static ?string $model = Vendor::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';
    protected static ?string $navigationGroup = 'Finance Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('img')->label('Profile Picture')->image()->columnSpanFull()->imageEditor()->extraAttributes(['style'=>'width:150px!important;border-radius:10px !important']),
                Forms\Components\TextInput::make('name')->required()->maxLength(255),
                Forms\Components\Select::make('vendor_type_id')->createOptionForm([
                    Forms\Components\TextInput::make('title')->required()->maxLength(255),
                    Forms\Components\Textarea::make('description')->columnSpanFull()->maxLength(255),
                    Forms\Components\Select::make('company_id')->columnSpanFull()->label('Company')->searchable()->preload()->options(Company::query()->pluck('title','id'))->required(),
                ])->createOptionUsing(function (array $data): int {
                    return VendorType::query()->create($data)->getKey();
                })->label('VendorType')->required()->searchable()->options(VendorType::query()->pluck('title','id')),
                Forms\Components\TextInput::make('NIC')->unique('vendors','NIC',ignoreRecord: true)->label('NIC')->nullable()->maxLength(255),
                Forms\Components\TextInput::make('phone')->tel()->numeric()->nullable()->maxLength(255),
                Forms\Components\TextInput::make('website')->nullable()->suffixIcon('heroicon-c-globe-americas')->maxLength(255),
                Forms\Components\TextInput::make('email')->unique('vendors','email',ignoreRecord: true)->email()->nullable()->maxLength(255),
                Forms\Components\ToggleButtons::make('gender')->boolean('Man','Woman')->required()->inline(),
                Forms\Components\Select::make('country')->nullable()->options(getCountry())->searchable()->preload(),
                Forms\Components\TextInput::make('state')->label('State/Province')->nullable()->maxLength(255),
                Forms\Components\TextInput::make('city')->nullable()->maxLength(255),
                Forms\Components\Select::make('company_id')->columnSpanFull()->label('Company')->searchable()->preload()->options(Company::query()->pluck('title','id'))->required(),
                Forms\Components\Textarea::make('description')->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->alignCenter(),
                Tables\Columns\TextColumn::make('phone')->searchable()->alignCenter(),
                Tables\Columns\TextColumn::make('email')->searchable()->alignCenter(),
                Tables\Columns\TextColumn::make('vendorType.title')->color('aColor')->url(fn($record)=>\App\Filament\Resources\VendorTypeResource::getUrl('index',['tableSearch'=>$record->vendorType->title]))->alignCenter(),
                Tables\Columns\TextColumn::make('amount')->alignCenter()->state(fn($record)=> $record->expenses->sum('amount'))->sortable(query: function (Builder $query, string $direction): Builder {return $query->withSum('expenses','amount')->orderBy('expenses_sum_amount',$direction);})->badge()->numeric(),
                Tables\Columns\TextColumn::make('total_amount')->alignCenter()->label('Balance')->sortable()->badge()->numeric(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('vendor_type_id')->label('VendorType')->searchable()->preload()->options(VendorType::query()->pluck('title','id'))
            ],getModelFilter())
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
            'index' => Pages\ListVendors::route('/'),
            'create' => Pages\CreateVendor::route('/create'),
            'edit' => Pages\EditVendor::route('/{record}/edit'),
        ];
    }
}
