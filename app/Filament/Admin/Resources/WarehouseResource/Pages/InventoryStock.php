<?php

namespace App\Filament\Admin\Resources\WarehouseResource\Pages;

use App\Filament\Admin\Resources\WarehouseResource;
use App\Models\Stock;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

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
                    Forms\Components\TextInput::make('quantity')->minValue(1)->required()->numeric(),
                    Forms\Components\ToggleButtons::make('type')->boolean('Stock In', 'Stock Out')->grouped()->required(),
                ])->columns(3),
                Forms\Components\Textarea::make('description')->required()->maxLength(255)->columnSpanFull(),
            ]);
    }


    public function table(Table $table): Table
    {
        return $table->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('employee.fullName'),
                Tables\Columns\TextColumn::make('inventory.product.info'),
                Tables\Columns\TextColumn::make('description')->searchable(),
                Tables\Columns\TextColumn::make('quantity')->badge(),
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
                Tables\Actions\CreateAction::make()->action(function ($data) {
                    $quantity = (int)$data['quantity'];
                    $inventory = \App\Models\Inventory::query()->firstWhere('id', $data['inventory_id']);
                    if ($data['type'] === "1") {
                        $inventory->update(['quantity' => $inventory->quantity + $quantity]);
                    } else {
                        if ($inventory->quantity - $quantity < 0) {
                            return Notification::make('error')->danger()->title('Quantity Not Valid')->send();
                        }
                        $inventory->update(['quantity' => $inventory->quantity - $quantity]);
                    }
                    Stock::query()->create([
                        'quantity' => $quantity,
                        'type' => $data['type'],
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
            ]);
    }
}
