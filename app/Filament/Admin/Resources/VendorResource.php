<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\VendorResource\Pages;
use App\Filament\Admin\Resources\VendorResource\RelationManagers;
use App\Models\Bank_category;
use App\Models\Vendor;
use App\Models\VendorType;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Facades\Filament;
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
    protected static ?string $navigationGroup = 'Finance';
    public static function canAccess(): bool
    {
        return false;
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('img')->label('Logo\Profile Picture')->image()->columnSpanFull()->imageEditor()->extraAttributes(['style'=>'width:150px!important;border-radius:10px !important']),
                Forms\Components\TextInput::make('name')->label('Company/Name')->required()->maxLength(255),
                SelectTree::make('vendor_type_id')->createOptionForm([
                    Forms\Components\Section::make([
                        Forms\Components\TextInput::make('title')->required()->maxLength(255),
                        Forms\Components\Select::make('parent_id')->label('Parent')->searchable()->preload()->options(VendorType::query()->where('type',0)->where('company_id',getCompany()->id)->pluck('title','id')),
                        Forms\Components\Textarea::make('description')->columnSpanFull()->maxLength(255),
                    ])->columns()
                ])->createOptionUsing(function (array $data): int {
                    $data['company_id'] = getCompany()->id;
                    $data['type'] = 0;
                    return VendorType::query()->create($data)->getKey();
                })->label('VendorType')->withCount()->defaultOpenLevel(2)->required()->searchable()->relationship('vendorType','title','parent_id', modifyQueryUsing: fn($query) => $query->where('type',0)->where('company_id',getCompany()->id), modifyChildQueryUsing: fn($query) => $query->where('type',0)->where('company_id',getCompany()->id)),
                Forms\Components\TextInput::make('NIC')->unique('vendors','NIC',ignoreRecord: true)->label('NIC')->nullable()->maxLength(255),
                Forms\Components\TextInput::make('phone')->tel()->numeric()->nullable()->maxLength(255),
                Forms\Components\TextInput::make('website')->nullable()->suffixIcon('heroicon-c-globe-americas')->maxLength(255),
                Forms\Components\TextInput::make('email')->unique('vendors','email',ignoreRecord: true)->email()->nullable()->maxLength(255),
                Forms\Components\Select::make('country')->nullable()->options(getCountry())->searchable()->preload(),
                Forms\Components\TextInput::make('state')->label('State/Province')->nullable()->maxLength(255),
                Forms\Components\TextInput::make('city')->columnSpanFull()->nullable()->maxLength(255),
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
                Tables\Columns\TextColumn::make('vendorType.title')->color('aColor')->url(fn($record)=>VendorTypeResource::getUrl('index',['tableSearch'=>$record->vendorType->title]))->alignCenter(),
                Tables\Columns\TextColumn::make('amount')->alignCenter()->state(fn($record)=> $record->expenses->sum('amount'))->sortable(query: function (Builder $query, string $direction): Builder {return $query->withSum('expenses','amount')->orderBy('expenses_sum_amount',$direction);})->badge()->numeric(),
                Tables\Columns\TextColumn::make('total_amount')->alignCenter()->label('Balance')->sortable()->badge()->numeric(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('vendor_type_id')->label('VendorType')->searchable()->preload()->options(VendorType::query()->where('company_id',getCompany()->id)->pluck('title','id'))
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
