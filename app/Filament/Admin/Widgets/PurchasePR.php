<?php

namespace App\Filament\Admin\Widgets;

use App\Filament\Admin\Resources\PurchaseOrderResource;
use App\Models\PurchaseRequest;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PurchasePR extends BaseWidget
{
    protected int | string | array $columnSpan=2;

    public function table(Table $table): Table
    {
        return $table->heading('PR Need To PO')
            ->query(
               PurchaseRequest::query()->where('company_id',getCompany()->id)->where('status','Approval')->whereHas('purchaseOrder',function (){},'!=')
            )
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex()->label('No'),
                Tables\Columns\TextColumn::make('employee.fullName')->tooltip(fn($record)=>$record->employee->position->title)
                    ->label('Requested By')->searchable(),
                Tables\Columns\TextColumn::make('purchase_number')->prefix('ATGT/UNC/')->label('PR No')->searchable(),
                Tables\Columns\TextColumn::make('description')->tooltip(fn($record) => $record->description)->limit(30),
                Tables\Columns\TextColumn::make('department')->state(fn($record) => $record->employee->department->title),
                Tables\Columns\TextColumn::make('request_date')->label('Request Date')->dateTime()->sortable(),
                // Tables\Columns\TextColumn::make('location')->state(fn($record) => $record->employee?->structure?->title)->numeric()->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->state(fn($record)=> match ($record->status->name){
                        'Approval'=>"Approved",
                        "Clarification"=>"Clarified",
                        "Verification"=>"Verified",
                        default=>$record->status->name
                    })->color(fn($state)=>match ($state){
                        "Approved"=>'success',
                        "Clarified"=>'success',
                        "Verified"=>'success',
                        "Finished"=>'success',
                        'Rejected'=>'danger',
                        'Requested'=>"primary"
                    })
                    ->sortable()->badge(),
                Tables\Columns\TextColumn::make('bid.quotation.party.name')->label('Vendor'),
                Tables\Columns\TextColumn::make('total')->alignCenter()->label('Total EST Price ' )

                    ->state(function ($record) {
                        $total = 0;
                        foreach ($record->items as $item) {
                            $total += $item->quantity * $item->estimated_unit_cost;
                        }
                        return number_format($total,2) ." " .$record->currency?->symbol;
                    })->numeric(),
                Tables\Columns\TextColumn::make('bid.total_cost')->alignCenter()->label('Total Final Price' )->numeric(),
            ])->actions([
                Tables\Actions\Action::make('Order')
                    ->disabled(fn() => getPeriod() === null)->tooltip(fn() => getPeriod() !== null ?'': 'Financial Period Required')
                    ->visible(fn($record) => $record->status->value == 'Approval')
                    ->icon('heroicon-s-shopping-cart')
                    ->url(fn($record) => PurchaseOrderResource::getUrl('create') . "?prno=" . $record->id),
            ]);
    }
}
