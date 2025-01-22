<?php

namespace App\Filament\Admin\Resources\EmployeeResource\RelationManagers;

use App\Models\Asset;
use App\Models\AssetEmployee;
use App\Models\Structure;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AssetEmployeeItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'assetEmployeeItems';
    protected static ?string $recordTitleAttribute = 'id';
    protected static ?string $title="Employee Assets";

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('id')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\ImageColumn::make('asset.product.image')->label('Item Photo'),
                Tables\Columns\TextColumn::make('asset.product.title')->label('Item Name')->searchable(),
                Tables\Columns\TextColumn::make('asset.sku')->label('SKU'),
                Tables\Columns\TextColumn::make('asset.serial_number')->label('Serial Number'),
                Tables\Columns\TextColumn::make('assetEmployee.approve_date')->label('Distribution Date')->date(),
                Tables\Columns\TextColumn::make('return_date')->label('Return Date')->date(),


            ])
            ->filters([
                //
            ])
            ->headerActions([

            ])
            ->actions([])
            ->bulkActions([
                Tables\Actions\BulkAction::make('return')->label('Return To Warehouse')

                    ->modalHeading('Return Asset')
                    ->form(function ($records){
                       return [
                            Forms\Components\Section::make([
                                Forms\Components\Textarea::make('reason')->label('Description')->placeholder('Enter the reason for returning the asset.')->required(),
                                Forms\Components\DatePicker::make('date')->default(now())->required(),
                                Forms\Components\Select::make('warehouse_id')->live()->label('Location')->options(getCompany()->warehouses()->pluck('title', 'id'))->required()->searchable()->preload(),
                                SelectTree::make('structure_id')->label('Address')->defaultOpenLevel(2)->model(Structure::class)->relationship('parent', 'title', 'parent_id', modifyQueryUsing: function ($query, Forms\Get $get) {
                                    return $query->where('warehouse_id', $get('warehouse_id'));
                                })->required(),

                            ])->columns(),
                            Forms\Components\Repeater::make('AssetEmployeeItem')->model(AssetEmployee::class)->relationship('assetEmployeeItem')->schema([
                                Forms\Components\Select::make('asset_id')
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->live()->label('Asset')->options(function () {
                                        $data = [];
                                        $assets = Asset::query()->with('product')->where('company_id', getCompany()->id)->get();
                                        foreach ($assets as $asset) {
                                            $data[$asset->id] = $asset->product?->title . " ( SKU #" . $asset->sku . " )";
                                        }
                                        return $data;
                                    })->required()->searchable()->preload(),

                            ])->formatStateUsing(function ()use($records){

                              return  $records->toArray();
                            })->columns(1)->addable(false)->deletable(false)->grid()
                        ];
                    })
                    ->action(function (array $data, $records) {

                        $AssetEmployee = AssetEmployee::query()->create([
                            'employee_id' => $this->getOwnerRecord()->getKey(),
                            'date' => $data['date'],
                            'type' => 'Returned',
                            'status' => 'Pending',
                            'description' => $data['reason'],
                            'company_id' => getCompany()->id,
                        ]);
                        foreach ($records as $record) {
                            $record->update([
                                'return_date'=>$data['date']
                            ]);
                            $AssetEmployee->assetEmployeeItem()->create([
                                'asset_employee_id' => $AssetEmployee->id,
                                'asset_id' => $record->asset_id,
                                'warehouse_id' => $data['warehouse_id'],
                                'structure_id' => $data['structure_id'],
                                'company_id' => getCompany()->id,
                            ]);
                        }
                    Notification::make('success')->success()->title("Your Request is Send")->send();
                    })->color('danger'),
//                Tables\Actions\BulkAction::make('Request Repair')->color('warning') ->modalHeading('Request Repair')
//                    ->form(function ($records){
//                        return [
//                            Forms\Components\Section::make([
//                                Forms\Components\Textarea::make('reason')->label('Reason')->placeholder('Enter the reason for returning the asset.')->required(),
//                                Forms\Components\DatePicker::make('date')->default(now())->required(),
//
//                            ])->columns(),
//                            Forms\Components\Repeater::make('AssetEmployeeItem')->deletable(false)->model(AssetEmployee::class)->relationship('assetEmployeeItem')->schema([
//                                Forms\Components\Select::make('asset_id')
//                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
//                                    ->live()->label('Asset')->options(function () {
//                                        $data = [];
//                                        $assets = Asset::query()->with('product')->where('company_id', getCompany()->id)->get();
//                                        foreach ($assets as $asset) {
//                                            $data[$asset->id] = $asset->product?->title . " ( SKU #" . $asset->sku . " )";
//                                        }
//                                        return $data;
//                                    })->required()->searchable()->preload(),
//
//                            ])->formatStateUsing(function ()use($records){
//
//                                return  $records->toArray();
//                            })->columns(1)->addable(false)->grid()
//                        ];
//                    })
//                    ->action(function (array $data, $records) {
//
//                        $AssetEmployee = AssetEmployee::query()->create([
//                            'employee_id' => $this->getOwnerRecord()->getKey(),
//                            'date' => $data['date'],
//                            'type' => 'Repair',
//                            'status' => 'Pending',
//                            'description' => $data['reason'],
//                            'company_id' => getCompany()->id,
//                        ]);
//                        foreach ($records as $record) {
//                            $record->update([
//                                'repair_date'=>$data['date']
//                            ]);
//                            $AssetEmployee->assetEmployeeItem()->create([
//                                'asset_employee_id' => $AssetEmployee->id,
//                                'asset_id' => $record->asset_id,
//                                'company_id' => getCompany()->id,
//                            ]);
//                        }
//                        Notification::make('success')->success()->title("Your Request is Send")->send();
//                    })

            ]);
    }
}
