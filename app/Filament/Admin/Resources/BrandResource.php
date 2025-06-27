<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\BrandResource\Pages;
use App\Filament\Admin\Resources\BrandResource\RelationManagers;
use App\Filament\Clusters\StackManagementSettings;
use App\Models\Brand;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;
    protected static ?string $navigationGroup = 'Logistic Management';
    protected static ?string $cluster = StackManagementSettings::class;
    protected static ?string $navigationIcon = 'heroicon-s-flag';
    protected static ?string $label="Brands";
    protected static ?string $pluralLabel="Brands ";

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')->columnSpanFull()->required()->maxLength(255)->label('Add New Brand'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->defaultSort('id', 'desc')->headerActions([
            ExportAction::make()
            ->after(function (){
                if (Auth::check()) {
                    activity()
                        ->causedBy(Auth::user())
                        ->withProperties([
                            'action' => 'export',
                        ])
                        ->log('Export' . "Brands");
                }
            })->exports([
                ExcelExport::make()->askForFilename("Brand")->withColumns([
                    Column::make('title'),
                    Column::make('id')->formatStateUsing(fn ($record)=>number_format($record->assets->count()))->heading('Quantity'),
                ]),
            ])->label('Export Brand')->color('purple')
        ])
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable(),
                Tables\Columns\TextColumn::make('asset')->url(fn($record)=>AssetResource::getUrl('index',['tableFilters[brand_id][value]'=>$record->id]))->color('aColor')->badge()->alignCenter()->state(fn ($record)=>number_format($record->assets->count()))->label('Quantity'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->modelLabel('Edit Brand'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([

                ExportBulkAction::make()
            ->after(function (){
                if (Auth::check()) {
                    activity()
                        ->causedBy(Auth::user())
                        ->withProperties([
                            'action' => 'export',
                        ])
                        ->log('Export' . "Brands");
                }
            })->exports([
                ExcelExport::make()->askForFilename("Brand")->withColumns([
                    Column::make('title'),
                    Column::make('id')->formatStateUsing(fn ($record)=>number_format($record->assets->count()))->heading('Quantity'),
                ]),
            ])->label('Export Brand')->color('purple')

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
            'index' => Pages\ListBrands::route('/'),
//            'create' => Pages\CreateBrand::route('/create'),
//            'edit' => Pages\EditBrand::route('/{record}/edit'),
        ];
    }
}
