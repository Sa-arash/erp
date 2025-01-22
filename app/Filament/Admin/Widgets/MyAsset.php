<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Asset;
use App\Models\AssetEmployee;
use App\Models\AssetEmployeeItem;
use App\Models\Employee;
use App\Models\Structure;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;

class MyAsset extends BaseWidget
{


    protected static ?string $recordTitleAttribute = 'id';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
               AssetEmployeeItem::query()->where('type',0)->whereHas('assetEmployee',function ($query){
                   return $query->where('employee_id',auth()->user()->employee->id)->where('type','Assigned');
               })
            )
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\ImageColumn::make('asset.product.image')->label('Item Photo'),
                Tables\Columns\TextColumn::make('product')->label('Product')
                ->state(fn($record)=>$record->asset->product->title."-".$record->asset->brand->title."-".$record->asset->model),
                Tables\Columns\TextColumn::make('warehouse.title')->label('Warehouse/Building')->sortable(),
                Tables\Columns\TextColumn::make('structure.title')->label('Location')->sortable(),
                Tables\Columns\TextColumn::make('asset.serial_number')->label('Serial Number'),
                Tables\Columns\TextColumn::make('assetEmployee.approve_date')->label('Distribution Date')->date(),
                Tables\Columns\TextColumn::make('return_date')->label('Return Date')->date(),
            ])
            // ->actions([
            //     Action::make('view')->infolist([
            //         TextEntry::make('request_date')->date(),
            //         TextEntry::make('purchase_number')->badge(),
            //         TextEntry::make('employee.department.title')->label('Department'),
            //         TextEntry::make('employee.fullName'),
            //         TextEntry::make('employee.structure.title')->label('Location'),
            //         TextEntry::make('comment')->label('Location'),
            //     ])
            // ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('return')->label('Return To Warehouse')

                    ->modalHeading('Return Asset')
                    ->form(function ($records) {
                        return [
                            Section::make([
                                Textarea::make('reason')->label('Description')->placeholder('Enter the reason for returning the asset.')->required(),
                                DatePicker::make('date')->default(now())->required(),
                                Select::make('warehouse_id')->live()->label('Location')->options(getCompany()->warehouses()->pluck('title', 'id'))->required()->searchable()->preload(),
                                SelectTree::make('structure_id')->label('Address')->defaultOpenLevel(2)->model(Structure::class)->relationship('parent', 'title', 'parent_id', modifyQueryUsing: function ($query, Get $get) {
                                    return $query->where('warehouse_id', $get('warehouse_id'));
                                })->required(),
                            ])->columns(),
                            Repeater::make('AssetEmployeeItem')->label('Assets')->model(AssetEmployee::class)->relationship('assetEmployeeItem')->schema([
                                Select::make('asset_id')
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->live()->label('Asset')->options(function () {
                                        $data = [];
                                        $assets = Asset::query()->with('product')->where('company_id', getCompany()->id)->get();
                                        foreach ($assets as $asset) {
                                            $data[$asset->id] = $asset->product?->title . " ( SKU #" . $asset->sku . " )";
                                        }
                                        return $data;
                                    })->required()->searchable()->preload(),
                            ])->formatStateUsing(function () use ($records) {
                                // بررسی کنید که آیا $records شامل داده‌های مورد انتظار است
                                if ($records->isEmpty()) {
                                    throw new \Exception('No records selected.');
                                }

                                return $records->toArray();
                            })->columns(1)->addable(false)->deletable(false)->grid()
                        ];
                    })
                    ->action(function (array $data, $records) {
                        // بررسی کنید که آیا $records شامل داده‌های مورد انتظار است
                        if ($records->isEmpty()) {
                            throw new \Exception('No records selected.');
                        }

                        $AssetEmployee = AssetEmployee::query()->create([
                            'employee_id' => auth()->user()->employee->id,
                            'date' => $data['date'],
                            'type' => 'Returned',
                            'status' => 'Pending',
                            'description' => $data['reason'],
                            'company_id' => getCompany()->id,
                        ]);

                        foreach ($records as $record) {
                            $record->update([
                                'return_date' => $data['date']
                            ]);

                            $AssetEmployee->assetEmployeeItem()->create([
                                'asset_employee_id' => $AssetEmployee->id,
                                'asset_id' => $record->asset_id,
                                'warehouse_id' => $record->warehouse_id,
                                'structure_id' => $record->structure_id,
                                'company_id' => getCompany()->id,
                            ]);
                        }

                        Notification::make('success')->success()->title("Your Request is Send")->send();
                    })->color('danger'),
            ]);
    }
}
