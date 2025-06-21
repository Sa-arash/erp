<?php

namespace App\Filament\Admin\Widgets;

use App\Filament\Admin\Resources\PurchaseOrderResource;
use App\Models\Asset;
use App\Models\AssetEmployeeItem;
use App\Models\PurchaseOrder;
use App\Models\Service;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Support\Enums\IconSize;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class MyAsset extends BaseWidget
{


    protected static ?string $recordTitleAttribute = 'id';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('No Asset')
            ->query(
                function () {
                    return AssetEmployeeItem::query()->where('type', 'Assigned');
                }
            )->filters([
                Tables\Filters\TernaryFilter::make('All')->label('Data Filter ')
                    ->placeholder('Only Me')->searchable()
                    ->trueLabel(' Subordinates')
                    ->falseLabel('Only Me')
                    ->queries(
                         function (Builder $query) {
                            $sub = AssetEmployeeItem::selectRaw('MAX(id) as id')
                                ->whereHas('assetEmployee', function ($q) {
                                    $q->whereIn('employee_id', getSubordinate());
                                })
                                ->groupBy('asset_id');
                            return $query->whereIn('id', $sub);
                        },
                        function (Builder $query) {
                            $sub = AssetEmployeeItem::selectRaw('MAX(id) as id')
                                ->whereHas('assetEmployee', function ($q) {
                                    $q->where('employee_id', getEmployee()->id);
                                })
                                ->groupBy('asset_id');

                            return $query->whereIn('id', $sub);
                        },
                        function (Builder $query) {
                            $sub = AssetEmployeeItem::selectRaw('MAX(id) as id')
                                ->whereHas('assetEmployee', function ($q) {
                                    $q->where('employee_id', getEmployee()->id);
                                })
                                ->groupBy('asset_id');

                            return $query->whereIn('id', $sub);
                        },
                    )

            ])
            ->columns([
                Tables\Columns\TextColumn::make('')->label('No')->rowIndex(),
                Tables\Columns\TextColumn::make('assetEmployee.employee.fullName')->searchable(),
                Tables\Columns\ImageColumn::make('asset.media.original_url')->state(function ($record) {
                    return $record->asset->media?->where('collection_name', 'images')->first()?->original_url;
                })->disk('public')
                    ->defaultImageUrl(fn($record) => asset('img/defaultAsset.png'))
                    ->alignLeft()->label('Asset Picture')->width(50)->height(50)->extraAttributes(['style' => 'border-radius:50px!important']),
                Tables\Columns\TextColumn::make('asset.number')->state(fn() => '_______________')->label('Barcode')->searchable()->description(function ($record) {

                    $barcode = '<img src="data:image/png;base64,' . \Milon\Barcode\Facades\DNS1DFacade::getBarcodePNG($record->asset->number, 'C39', 1, 20) . '" alt="barcode"/>';
                    $barcode .= "<p style='text-align: center'>{$record->asset->number}</p>";
                    return new HtmlString($barcode);
                })->action(function ($record) {
                    return redirect(route('pdf.barcode', ['code' => $record->id]));
                }),
                Tables\Columns\TextColumn::make('purchase_order_id')->label('PO No')->state(fn($record) => $record->purchase_order_id === null ? "---" : PurchaseOrder::find($record->purchase_order_id)->purchase_orders_number)
                    ->url(fn($record) => $record->purchase_order_id ? PurchaseOrderResource::getUrl() . "?tableFilters[id][value]=" . $record->purchase_order_id : false),
                Tables\Columns\TextColumn::make('asset.titlen')->label('Asset Description'),
                Tables\Columns\TextColumn::make('asset.brand.title'),
                Tables\Columns\TextColumn::make('asset.type')->label('Type'),
                Tables\Columns\TextColumn::make('asset.serial_number')->label('Serial Number'),
                Tables\Columns\TextColumn::make('asset.warehouse.title')->label('Warehouse/Building')->sortable(),
                Tables\Columns\TextColumn::make('asset.structure')->state(function ($record) {
                    $str = getParents($record->structure);
                    return substr($str, 1, strlen($str) - 1);
                })->label('Location')->sortable(),
                Tables\Columns\TextColumn::make('asset.manufacturer')->label('Manufacturer'),
                Tables\Columns\TextColumn::make('created_at')->label('Distribution Date')->dateTime(),
                Tables\Columns\TextColumn::make('return_date')->label('Return Date')->date(),
            ])
             ->actions([
                 Tables\Actions\Action::make('pdf')->tooltip('Print')->icon('heroicon-s-printer')->iconSize(IconSize::Medium)->label('')
                     ->url(fn($record) => route('pdf.asset', ['id' => $record->asset_id]))->openUrlInNewTab(),
//                 Action::make('view')->infolist([
//                     TextEntry::make('request_date')->date(),
//                     TextEntry::make('purchase_number')->badge(),
//                     TextEntry::make('employee.department.title')->label('Department'),
//                     TextEntry::make('employee.fullName'),
//                     TextEntry::make('employee.structure.title')->label('Location'),
//                     TextEntry::make('comment')->label('Location'),
//                 ])
                 Tables\Actions\Action::make('Service')->label('Request Maintenance')
                 ->hidden(fn($record)=>($record->asset->status === 'underRepair') or $record->asset?->service?->where('status','Pending')->count() or $record->assetEmployee->employee_id ===getEmployee()->id )
                 ->fillForm(function ($record){
                        return [
                            'asset_id'=>$record->asset_id,
                            'request_date'=>now()
                            ];
                 })->form(function ($record){
                     return [
                         Section::make([
                             DatePicker::make('request_date')->required()->label('Request Date'),
                             Select::make('asset_id')->required()->label('Asset')->searchable()->preload()->options(Asset::query()->where('id',$record->asset_id)->get()->pluck('title','id')),
                             Textarea::make('note')->nullable()->columnSpanFull(),
                             FileUpload::make('images')->columnSpanFull()->image()->multiple()->nullable()
                         ])->columns()
                     ];
                 })->action(function ($data){
                     $service = Service::query()->create([
                         'company_id'=>getCompany()->id,
                         'employee_id'=>getEmployee()->id,
                         'request_date'=>$data['request_date'],
                         'asset_id'=>$data['asset_id'],
                         'note'=>$data['note']
                     ]);
                         $mediaItems = $data['images'] ?? [];
                         foreach ($mediaItems as $mediaItem) {
                             $service->addMedia(public_path('images/'.$mediaItem))->toMediaCollection('images');
                         }
                     Notification::make('success')->title('Service Request Is Sent')->color('success')->success()->send();
                 })
             ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('print')->label('Print ')->iconSize(IconSize::Large)->icon('heroicon-s-printer')->color('primary')->action(function ($records,$data) {

                    return redirect(route('pdf.assets', ['ids' => implode('-', $records->pluck('asset_id')->toArray()), 'company' => getCompany()->id,'type'=>$data['by']]));
                })->form([
                    Select::make('by')->required()->default('warehouse_id')->label('Asset By')->options(['warehouse_id'=>'Location','brand_id'=>'Brand','type'=>'Type','po_number'=>'PO','party_id'=>'Vendor'])->searchable()->preload()
                ]),

//                Tables\Actions\BulkAction::make('return')->label('Return To Warehouse')
//                    ->modalHeading('Return Asset')
//                    ->form(function ($records) {
//                        return [
//                            Section::make([
//                                Textarea::make('reason')->label('Description')->placeholder('Enter the reason for returning the asset.')->required(),
//                                DatePicker::make('date')->default(now())->required(),
//                                Select::make('warehouse_id')->live()->label('Location')->options(getCompany()->warehouses()->pluck('title', 'id'))->required()->searchable()->preload(),
//                                SelectTree::make('structure_id')->label('Address')->defaultOpenLevel(2)->model(Structure::class)->relationship('parent', 'title', 'parent_id', modifyQueryUsing: function ($query, Get $get) {
//                                    return $query->where('warehouse_id', $get('warehouse_id'));
//                                })->required(),
//                            ])->columns(),
//                            Repeater::make('AssetEmployeeItem')->label('Assets')->model(AssetEmployee::class)->relationship('assetEmployeeItem')->schema([
//                                Select::make('asset_id')
//                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
//                                    ->live()->label('Asset')->options(function () {
//                                        $data = [];
//                                        $assets = Asset::query()->with('product')->whereHas('employees', function ($query) {
//                                            return $query->where('return_date', null)->where('return_approval_date', null)->whereHas('assetEmployee', function ($query) {
//                                                return $query->where('employee_id', getEmployee()->id);
//                                            });
//                                        })->where('company_id', getCompany()->id)->get();
//                                        foreach ($assets as $asset) {
//                                            $data[$asset->id] = $asset->product?->title . " ( SKU #" . $asset->product?->sku . " )";
//                                        }
//                                        return $data;
//                                    })->required()->searchable()->preload(),
//                            ])->formatStateUsing(function () use ($records) {
//                                // بررسی کنید که آیا $records شامل داده‌های مورد انتظار است
//                                if ($records->isEmpty()) {
//                                    throw new \Exception('No records selected.');
//                                }
//
//                                return $records->toArray();
//                            })->columns(1)->addable(false)->deletable(false)->grid()
//                        ];
//                    })
//                    ->action(function (array $data, $records) {
//                        // بررسی کنید که آیا $records شامل داده‌های مورد انتظار است
//                        if ($records->isEmpty()) {
//                            throw new \Exception('No records selected.');
//                        }
//
//                        $AssetEmployee = AssetEmployee::query()->create([
//                            'employee_id' => auth()->user()->employee->id,
//                            'date' => $data['date'],
//                            'type' => 'Returned',
//                            'status' => 'Pending',
//                            'description' => $data['reason'],
//                            'company_id' => getCompany()->id,
//                        ]);
//
//                        foreach ($records as $record) {
//                            $record->update([
//                                'return_date' => $data['date']
//                            ]);
//
//                            $AssetEmployee->assetEmployeeItem()->create([
//                                'asset_employee_id' => $AssetEmployee->id,
//                                'asset_id' => $record->asset_id,
//                                'warehouse_id' => $record->warehouse_id,
//                                'structure_id' => $record->structure_id,
//                                'company_id' => getCompany()->id,
//                            ]);
//                        }
//
//                        Notification::make('success')->success()->title("Your Request is Send")->send();
//                    })->color('danger'),
                Tables\Actions\BulkAction::make('Take Out')->modalWidth(MaxWidth::SixExtraLarge)->color('warning')->form(function ($records) {
                    return [
                        Section::make([
                            TextInput::make('from')->label('From (Location)')->default(getEmployee()->structure?->title)->required()->maxLength(255),
                            TextInput::make('to')->label('To (Location)')->required()->maxLength(255),
                            DatePicker::make('date')->default(now())->required()->label('CheckOut Date'),
                            DatePicker::make('return_date')->label('CheckIn Date'),
                            Textarea::make('reason')->columnSpanFull()->required(),
                            ToggleButtons::make('status')->default('Returnable')->colors(['Returnable' => 'success', 'Non-Returnable' => 'danger'])->live()->required()->grouped()->options(['Returnable' => 'Returnable', 'Non-Returnable' => 'Non-Returnable']),
                            ToggleButtons::make('type')->default('Modification')->required()->grouped()->options(function (Get $get) {
                                if ($get('status') === "Returnable") {
                                    return ['Modification' => 'Modification'];
                                } else {
                                    return ['Personal Belonging' => 'Personal Belonging', 'Domestic Waste' => 'Domestic Waste', 'Construction Waste' => 'Construction Waste'];
                                }
                            }),
                            Repeater::make('items')->label('Registered Asset')->orderable(false)->schema([
                                Select::make('asset_id')
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->live()->label('Asset')->options(function () {
                                        $data = [];
                                        $sub = AssetEmployeeItem::selectRaw('MAX(id) as id')
                                            ->whereHas('assetEmployee', function ($q) {
                                                $q->where('employee_id', getEmployee()->id);
                                            })
                                            ->groupBy('asset_id');

                                         $assetA=AssetEmployeeItem::query()
                                            ->whereIn('id', $sub)
                                            ->where('type', 'Assigned')->pluck('asset_id')->toArray();

                                        $assets = Asset::query()->with('product')->whereIn('id',$assetA)->whereHas('employees', function ($query) {
                                            return $query->whereHas('assetEmployee', function ($query) {
                                                return $query->where('employee_id', getEmployee()->id);
                                            });
                                        })->where('company_id', getCompany()->id)->get();
                                        foreach ($assets as $asset) {
                                            $data[$asset->id] = $asset->product?->title." ".$asset->description . " ( SKU #" . $asset->product?->sku . " )";
                                        }
                                        return $data;
                                    })->required()->searchable()->preload(),
                                TextInput::make('remarks')->nullable()
                            ])->columnSpanFull()->columns()->formatStateUsing(function () use ($records) {
                                $data = [];
                                foreach ($records as $record) {
                                    if ($record->assetEmployee->employee_id ==getEmployee()->id){
                                        $data[] = ['asset_id' => $record->asset_id];
                                    }
                                }
                                return $data;
                            }),
                            Repeater::make('itemsOut')->label('Unregistered Asset')->orderable(false)->schema([
                                TextInput::make('name')->required(),
                                TextInput::make('remarks')->nullable(),
                            ])->columnSpanFull()->columns()
                        ])->columns(4)
                    ];
                })->action(function ($data) {
                    $id = getCompany()->id;
                    $data['company_id'] = $id;
                    $employee = getEmployee();

                    $data['employee_id'] = $employee->id;
                    $items = $data['items'];
                    unset($data['items']);
                    $takeOut = \App\Models\TakeOut::query()->create($data);
                    foreach ($items as $item) {
                        $item['company_id'] = $id;
                        $takeOut->items()->create($item);
                    }
                    sendAdmin($employee,$takeOut,getCompany());
                    Notification::make('success')->color('success')->success()->title('Request Sent')->send()->sendToDatabase(auth()->user());
                }),

            ]);
    }
}
