<?php

namespace App\Filament\Admin\Resources\ApprovalResource\Pages;

use App\Filament\Admin\Resources\ApprovalResource;
use App\Models\PurchaseOrderItem;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Support\Enums\IconSize;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ApprovePurchaseOrder extends ManageRelatedRecords
{
    protected static string $resource = ApprovalResource::class;

    protected static string $relationship = 'approvable';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationLabel(): string
    {
        return 'Approvable';
    }



    public function table(Table $table): Table
    {
        return $table
            ->query(PurchaseOrderItem::query()->where('purchase_order_id',$this->record->approvable_id))
            ->columns([
                Tables\Columns\TextColumn::make('#')->rowIndex(),
                Tables\Columns\TextColumn::make('product.info'),
                Tables\Columns\TextColumn::make('description'),
                Tables\Columns\TextColumn::make('unit.title'),
                Tables\Columns\TextColumn::make('quantity'),
                Tables\Columns\TextColumn::make('unit_price')->numeric(2)->label('Unit Price'),
                Tables\Columns\TextColumn::make('taxes')->label('Taxes'),
                Tables\Columns\TextColumn::make('freights')->label('Freights'),
                Tables\Columns\TextColumn::make('total')->label('Total')->numeric(2),

            ])
            ->filters([
                //
            ])
            ->headerActions([

                Tables\Actions\Action::make('infoPO')->label('View PO')->color('warning')->infolist(function (){
                    $record=$this->record->approvable;

                    return [
                        Section::make([
                            TextEntry::make('purchase_orders_number')->prefix('ATGT/UNC/')->state($record->purchase_orders_number)->label('PO NO'),
                            TextEntry::make('purchase_orders_number')->prefix('ATGT/UNC/')->state($record->purchaseRequest?->purchase_number)->label('PR NO'),
                            TextEntry::make('Currency')->state($record->currency->name)->label('Currency'),
                            TextEntry::make('Exchange Rate')->numeric(3)->state($record->exchange_rate)->label('Exchange Rate'),
                            TextEntry::make('date_of_po')->state($record->date_of_po)->label('Date of PO'),
                            TextEntry::make('vendor')->state($record->vendor->name.'('.$record->vendor?->accountVendor?->code.')')->label('Vendor'),
                            TextEntry::make('status')->state($record->status)->label('Status')->badge(),
                            TextEntry::make('invoice')->state($record->invoice?->name.'('.$record->invoice?->number.')')->label('Invoice'),
                            TextEntry::make('total')->state(number_format($record->items->sum('total'),2))->label('Total')->badge(),

                        ])->columns(),
                    ];
                }),
                Tables\Actions\Action::make('approve')->visible(function () {
                    return $this->record->status->value==="Pending";
                })->icon('heroicon-o-check-badge')->iconSize(IconSize::Large)->color('success')->form([
                    Forms\Components\ToggleButtons::make('status')->default('Approve')->colors(['Approve' => 'success', 'NotApprove' => 'danger'])->options(['Approve' => 'Approve','NotApprove' => 'Denied'])->grouped(),
                    Forms\Components\Textarea::make('comment')->nullable()
                ])->action(function ($data){
                    $record=$this->record;
                    $record->update(['comment' => $data['comment'], 'status' => $data['status'], 'approve_date' => now()]);
                    $PO = $record->approvable;
                    $PO->approvals()->whereNot('id', $record->id)->where('position', $record->position)->delete();
                    if ($data['status'] === "NotApprove") {
                        $PO->update([ 'status' => "rejected"]);
                    } else {
                        if ($PO->status === "pending") {
                            $PO->update([ 'status' => 'Approve Logistic Head']);
                        } else if ($PO->status === "Approve Logistic Head") {
                            $PO->update([ 'status' => 'Approve Verification']);
                        } elseif ($PO->status === "Approve Verification") {
                            $PO->update([ 'status' => 'Approved']);
                        }
                    }
                    if ($data['status'] === "Approve") {
                        if ($PO->status === "Approve Logistic Head") {
                            sendApprove($PO, 'PO Verification_approval');
                        } else if ($PO->status === "Approve Verification") {
                            sendApprove($PO, 'PO Approval_approval');
                        }
                    }
                    Notification::make('success')->success()->title('Answer '.$record->position.' PP NO : '.$PO->purchase_orders_number)->send()->sendToDatabase(auth()->user());

                })
            ])
            ->actions([

            ]);
    }
}
