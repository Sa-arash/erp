<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ServiceResource\Pages;
use App\Filament\Admin\Resources\ServiceResource\RelationManagers;
use App\Models\Asset;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Service Request')->schema([
                    Forms\Components\Select::make('employee_id')->live()
                            ->searchable()
                            ->preload()
                            ->required()
                            ->options(getCompany()->employees->pluck('fullName', 'id'))
                            ->default(fn() => auth()->user()->employee->id),


                            Select::make('asset_id')
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                            ->live()->label('Asset')->options(function (Get $get) {
                                if($get('employee_id'))
                                {

                                    $employeeID=$get('employee_id');
                                    $data = [];
                                    $assets = Asset::query()->with('product')->whereHas('employees', function ($query) use($employeeID) {
                                        return $query->where('return_date', null)->where('return_approval_date', null)->whereHas('assetEmployee', function ($query)use($employeeID) {
                                            return $query->where('employee_id',$employeeID );
                                        });
                                    })->where('company_id', getCompany()->id)->get();
                                    foreach ($assets as $asset) {
                                        $data[$asset->id] = $asset->product?->title . " ( SKU #" . $asset->product?->sku . " )";
                                    }
                                    return $data;
                                }
                            })->required()->searchable()->preload(),

                Forms\Components\DatePicker::make('request_date')
                    ->required(),
                // Forms\Components\Select::make('type')->options(['On-site Service','Purchase Order','TakeOut For Reaper'])
                // Forms\Components\Select::make('status')
                    // ->required(),
                Forms\Components\FileUpload::make('images'),
                Forms\Components\DatePicker::make('answer_date'),
                Forms\Components\DatePicker::make('service_date'),
                Forms\Components\Textarea::make('note')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('reply')
                    ->columnSpanFull(),
                
                Forms\Components\TextInput::make('PO_number')
                    ->maxLength(255),
                Forms\Components\Hidden::make('company_id')
                   ->default(getCompany()->id)
                    ->required(),

                ])->columns(3),
                
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('request_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('answer_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('service_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('asset.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('PO_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('company.title')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
