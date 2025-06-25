<?php

namespace App\Filament\Admin\Resources\GrnResource\Widgets;

use App\Filament\Admin\Resources\GrnResource;
use App\Filament\Admin\Resources\PurchaseRequestResource;
use App\Models\PurchaseOrder;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PurchaseOrderGRN extends BaseWidget
{
    protected int | string | array $columnSpan='full';
    public function table(Table $table): Table
    {
        return $table
            ->query(
                PurchaseOrder::query()->where('company_id',getCompany()->id)->where('status','Approved')
            )
            ->columns([
                Tables\Columns\TextColumn::make('NO')->label('No')->rowIndex(),
                Tables\Columns\TextColumn::make('purchase_orders_number')
                    ->label('PO No')
                    ->searchable(),
                Tables\Columns\TextColumn::make('date_of_po')
                    ->label('Date Of PO')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->label('Total'),

                Tables\Columns\TextColumn::make('purchaseRequest.purchase_number')->badge()->url(fn($record) => PurchaseRequestResource::getUrl('index') . "?tableFilters[purchase_number][value]=" . $record->purchaseRequest?->id)->sortable()->label("PR No"),
                Tables\Columns\TextColumn::make('purchaseRequest.employee.fullName')->badge()->sortable()->label("PR Requester"),

                Tables\Columns\TextColumn::make('date_of_delivery')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location_of_delivery')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),

                Tables\Columns\Textcolumn::make('status')->state(fn($record)=>match ($record->status){
                    'pending'=>"Pending",
                    'Approved'=>"Approved",
                    'rejected'=>"Rending",
                    'GRN'=>'GRN',
                    'Approve Logistic Head'=>'Approve Review',
                    'GRN And inventory'=>'GRN And inventory',
                    'Inventory'=>'Inventory',
                    'Approve Verification'=>'Approve Verified',
                })->badge()
                    ->label('Status'),

            ])->actions([
                Tables\Actions\Action::make('GRN')->label('GRN')->url(fn($record)=>GrnResource::getUrl('create')."?PO=".$record->id)
            ]);
    }
}
