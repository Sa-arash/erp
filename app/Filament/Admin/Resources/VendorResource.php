<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\VendorResource\Pages;
use App\Filament\Admin\Resources\VendorResource\RelationManagers\PurchaseOrderRelationManager;
use App\Models\Parties;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use TomatoPHP\FilamentMediaManager\Form\MediaManagerInput;

class VendorResource extends Resource
    implements HasShieldPermissions

{
    protected static ?string $model = Parties::class;

    protected static ?string $navigationIcon = 'heroicon-s-users';
    protected static ?string $navigationGroup = 'Logistic Management';
    protected static ?string $label = 'Vendor';
    protected static ?int $navigationSort = 9;


    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'update',
        ];
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make([
                    Forms\Components\TextInput::make('name')->label('Company/Name')->required()->maxLength(255),
                    Forms\Components\TextInput::make('phone')->tel()->maxLength(255),
                    Forms\Components\TextInput::make('email')->email()->maxLength(255),
                    Forms\Components\ToggleButtons::make('status')->required()->grouped()->options(['Gray'=>'Gray','Green'=>'Green','Red'=>'Red'])->colors(['Green'=>'success','Red'=>'danger','Gray'=>'grayBack']),
                    Forms\Components\Textarea::make('address')->columnSpanFull(),
                ])->columns(4),

                MediaManagerInput::make('image')->label('Upload Logo')->orderable(false)->folderTitleFieldName("name")->image(true)
                    ->disk('public')
                    ->schema([
                    ])->maxItems(1)->addActionLabel('Add Logo'),
            ]);
    }

    public static function canAccess(): bool
    {
        return auth()->user()->can('view_any_vendor');
    }
    public static function canView(Model $record): bool
    {
        return auth()->user()->can('view_vendor') ;
    }
    public static function canEdit(Model $record): bool
    {
        return auth()->user()->can('update_vendor');
    }

    public static function table(Table $table): Table
    {
        return $table->query(Parties::query()->where('company_id', getCompany()->id)->whereIn('type', ['vendor', 'both']))
            ->columns([
                Tables\Columns\TextColumn::make('No')->width(10)->rowIndex(),
                Tables\Columns\ImageColumn::make('media.original_url')->state(function ($record) {
                    return $record->media->where('collection_name', 'image')->first()?->original_url;
                })->disk('public')->defaultImageUrl(fn($record) => asset('img/user.png'))->label('Vendor Image')->width(50)->height(50)->extraAttributes(['style' => 'border-radius:50px!important']),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('phone')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('address')->limit(30)->tooltip(fn($record)=>$record->address)->searchable(),
                Tables\Columns\TextColumn::make('purchase_order_items_count')->label('Count PO ')->alignCenter()->counts('purchaseOrderItems'),
                Tables\Columns\TextColumn::make('purchase_order_items_sum_total')->numeric(2)->label('Total PO ')->sum('purchaseOrderItems','total'),
                Tables\Columns\TextColumn::make('status')->color(fn($state)=> match ($state){'Green'=>'success','Red'=>'danger','Gray'=>'grayBack',})->badge(),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

            ])
            ->bulkActions([
//                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
//                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            PurchaseOrderRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVendors::route('/'),
            'create' => Pages\CreateVendor::route('/create'),
            'view' => Pages\ViewVendor::route('/{record}'),
            'edit' => Pages\EditVendor::route('/{record}/edit'),
        ];
    }
}
