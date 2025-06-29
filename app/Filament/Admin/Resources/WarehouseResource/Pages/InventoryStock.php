<?php

namespace App\Filament\Admin\Resources\WarehouseResource\Pages;

use App\Filament\Admin\Resources\WarehouseResource;
use App\Models\Package;
use App\Models\Stock;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
class InventoryStock extends ManageRelatedRecords
{
    protected static string $resource = WarehouseResource::class;

    protected static string $relationship = 'stocks';

    protected static ?string $navigationIcon = 'heroicon-c-arrow-down-on-square-stack';

    public static function getNavigationLabel(): string
    {
        return 'Stocks';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make([
                    Forms\Components\Select::make('inventory_id')->options(function () {
                        $inventories = getCompany()->inventories->where('warehouse_id', $this->record->id);
                        $data = [];
                        foreach ($inventories as $inventory) {
                            $data[$inventory->id] = $inventory->product->info;
                        }
                        return $data;
                    })->searchable()->preload()->label('Inventory')->required(),
                    Forms\Components\Select::make('package_id')->label('Package')->live()->searchable()->options(fn()=>getCompany()->packages->mapWithKeys(function ($item) {
                        return [$item->id => $item->title . ' (' . $item->quantity.')'];
                    }))->createOptionForm([
                        Forms\Components\TextInput::make('title')->required()->maxLength(255),
                        Forms\Components\TextInput::make('quantity')->required()->numeric(),
                    ])->createOptionUsing(function ($data){
                        $record= Package::query()->create(['title'=>$data['title'],'quantity'=>$data['quantity'],'company_id'=>getCompany()->id]);
                        Notification::make('success')->success()->title('Submitted Successfully')->send();
                        return $record->getKey();
                    }),
                    Forms\Components\TextInput::make('quantity')->minValue(1)->required()->numeric(),
                ])->columns(3),
                Forms\Components\Textarea::make('description')->required()->maxLength(255)->columnSpanFull(),
            ]);
    }


    public function table(Table $table): Table
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
                        ->log('Export' . "Stock");
                }
            })->exports([
                ExcelExport::make()->askForFilename("Stock")->withColumns([
                   Column::make('employee.fullName'),
                   Column::make('inventory.product.info'),
                   Column::make('description'),
                   Column::make('quantity'),
                   Column::make('package.title')->formatStateUsing(fn($record)=> isset($record->package?->quantity)? '('.$record->quantity /$record->package?->quantity.' * '. $record->package?->quantity .')'.$record->package->title:'---'),
                   Column::make('purchaseOrder.purchase_orders_number')->heading('PO NO'),
                   Column::make('type')->formatStateUsing(fn($record) => $record->type === 1 ? "Stock In" : "Stock Out"),
                   Column::make('transaction')->formatStateUsing(function($record){
                        if ($record->transaction){
                          return  $record->type ?"Stock In" : "Stock Out";
                        }

                    } ),
                   Column::make('created_at')->heading('Stock Date'),
                ]),
            ])->label('Export Stock')->color('purple')
        ])
            ->columns([
                Tables\Columns\TextColumn::make(getRowIndexName())->rowIndex(),
                Tables\Columns\TextColumn::make('employee.fullName'),
                Tables\Columns\TextColumn::make('inventory.product.info'),
                Tables\Columns\TextColumn::make('description')->searchable(),
                Tables\Columns\TextColumn::make('quantity')->badge(),
                Tables\Columns\TextColumn::make('package.title')->state(fn($record)=> isset($record->package?->quantity)? '('.$record->quantity /$record->package?->quantity.' * '. $record->package?->quantity .')'.$record->package->title:'---')->badge(),
                Tables\Columns\TextColumn::make('purchaseOrder.purchase_orders_number')->label('PO NO')->badge(),
                Tables\Columns\TextColumn::make('type')->state(fn($record) => $record->type === 1 ? "Stock In" : "Stock Out")->badge()->color(fn($state) => $state === "Stock In" ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('transaction')->state(function($record){
                    if ($record->transaction){
                      return  $record->type ?"Stock In" : "Stock Out";
                    }

                } )->badge()->color(fn($state) => $state === "Stock In" ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('created_at')->label('Stock Date')->dateTime(),
            ])
            ->filters([
                DateRangeFilter::make('created_at')->label('Stock Date'),
                Tables\Filters\TernaryFilter::make('type')->label('Type')->placeholder('All Type')->trueLabel('Stock In')->falseLabel('Stock Out')->searchable(),
                Tables\Filters\TernaryFilter::make('transaction')->label('Transaction')->placeholder('All Stocks')->trueLabel('Yes')->falseLabel('No')->searchable()
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->modalWidth(MaxWidth::SixExtraLarge)->label('New Stock OUT')->color('danger')->action(function ($data) {
                    $quantity = (int)$data['quantity'];
                    $inventory = \App\Models\Inventory::query()->firstWhere('id', $data['inventory_id']);

                        if ($inventory->quantity - $quantity < 0) {
                            return Notification::make('error')->danger()->title('Quantity Not Valid')->send();
                        }
                        $inventory->update(['quantity' => $inventory->quantity - $quantity]);
                    if (isset($data['package_id'])){
                        $package=Package::query()->firstWhere('id',$data['package_id']);
                        if ($package){
                            $quantity=$quantity*$package->quantity;
                        }
                    }
                        Stock::query()->create([
                            'quantity' => $quantity,
                            'type' => 0,
                            'package_id' => $data['package_id'],
                            'description' => $data['description'],
                            'employee_id' => getEmployee()->id,
                            'inventory_id' => $data['inventory_id']
                        ]);
                    Notification::make('success')->success()->title('Submitted Successfully')->send();
                }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->form([
                    Forms\Components\TextInput::make('quantity')->minValue(1)->required()->numeric(),
                    Forms\Components\Textarea::make('description')->required()->maxLength(255)->columnSpanFull(),
                ])->action(function ($data, $record) {
                    $quantity = (int)$data['quantity'];
                    $inventory = \App\Models\Inventory::query()->firstWhere('id', $record->inventory_id);
                    if ($quantity != $record->quantity) {
                        $result = $quantity - $record->quantity;
                        if ($record->type === 0) {
                            $inventory->update(['quantity' => $inventory->quantity - $result]);
                        } else {
                            $inventory->update(['quantity' => $inventory->quantity + $result]);
                        }
                    }
                    $record->update([
                        'quantity' => $quantity,
                        'description' => $data['description'],
                        'employee_id' => getEmployee()->id,
                    ]);
                    Notification::make('success')->success()->title('Submitted Successfully')->send();
                })
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
                        ->log('Export' . "Stock");
                }
            })->exports([
                ExcelExport::make()->askForFilename("Stock")->withColumns([
                   Column::make('employee.fullName'),
                   Column::make('inventory.product.info'),
                   Column::make('description'),
                   Column::make('quantity'),
                   Column::make('package.title')->formatStateUsing(fn($record)=> isset($record->package?->quantity)? '('.$record->quantity /$record->package?->quantity.' * '. $record->package?->quantity .')'.$record->package->title:'---'),
                   Column::make('purchaseOrder.purchase_orders_number')->heading('PO NO'),
                   Column::make('type')->formatStateUsing(fn($record) => $record->type === 1 ? "Stock In" : "Stock Out"),
                   Column::make('transaction')->formatStateUsing(function($record){
                        if ($record->transaction){
                          return  $record->type ?"Stock In" : "Stock Out";
                        }

                    } ),
                   Column::make('created_at')->heading('Stock Date'),
                ]),
            ])->label('Export Stock')->color('purple')
            ]);
    }
}
