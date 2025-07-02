<?php

namespace App\Filament\Admin\Resources\ApprovalResource\Pages;

use App\Filament\Admin\Resources\ApprovalResource;
use App\Models\PurchaseOrderItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Support\Enums\IconSize;
use Filament\Tables;
use Filament\Tables\Table;

class ApproveGRNItem extends ManageRelatedRecords
{
    protected static string $resource = ApprovalResource::class;

    protected static string $relationship = 'approvable';
    protected ?string $heading="Approve ";

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationLabel(): string
    {
        return 'Approvable';
    }

    public function table(Table $table): Table
    {

        return $table->query(PurchaseOrderItem::query()->where('purchase_order_id', $this->record->approvable?->purchase_order_id))
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make(getRowIndexName())->rowIndex(),
                Tables\Columns\TextColumn::make('product.info')->searchable(query: fn($query, $search) => $query->whereHas('product', function ($query) use ($search) {
                    return $query->where('title', 'like', "%{$search}%")->orWhere('second_title', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%");
                })),
                Tables\Columns\TextColumn::make('description')->wrap()->searchable(),
                Tables\Columns\TextColumn::make('unit.title')->searchable(),
                Tables\Columns\TextColumn::make('quantity')->summarize(Tables\Columns\Summarizers\Sum::make()->numeric()),
                Tables\Columns\TextColumn::make('unit_price')->numeric(2)->label('Unit Price'),
//                Tables\Columns\TextColumn::make('taxes')->label('Taxes'),
//                Tables\Columns\TextColumn::make('freights')->label('Freights'),
//                Tables\Columns\TextColumn::make('vendor.name')->label('Vendor'),
                Tables\Columns\TextColumn::make('currency.name')->label('Currency'),
                Tables\Columns\TextColumn::make('exchange_rate')->label('Exchange Rate'),
                Tables\Columns\TextColumn::make('employee.fullName'),
                Tables\Columns\TextColumn::make('receive_status')->color(fn($state) => match ($state) {
                    'Approved' => 'success',
                    'Rejected' => 'danger',
                    default=>"primary"
                })->badge(),
                Tables\Columns\TextColumn::make('receive_comment')->wrap(),
                Tables\Columns\TextColumn::make('total')->summarize(Tables\Columns\Summarizers\Sum::make()->numeric(2))->label('Total')->numeric(2),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\Action::make('approve')->visible(function () {
                    $record=$this->record;

                    return  $record->status->value ==="Pending" and PurchaseOrderItem::query()->where('purchase_order_id', $record->approvable?->purchase_order_id)->where('receive_status', '!=','Approved')->count()===0;
                })->icon('heroicon-o-check-badge')->iconSize(IconSize::Large)->color('success')->form([
                    Forms\Components\ToggleButtons::make('status')->required()->default('Approve')->colors(['Approve' => 'success', 'NotApprove' => 'danger'])->options(['Approve' => 'Approve', 'NotApprove' => 'Reject'])->grouped(),
                    Forms\Components\Textarea::make('comment')->nullable()
                ])->action(function ($data) {
                    $this->record->update(['status' => $data['status'], 'comment' => $data['comment'], 'approve_date' => now()]);
                    sendSuccessNotification();
                })->requiresConfirmation()
            ])
            ->actions([
                Tables\Actions\Action::make('receive')->visible(fn()=>$this->record->status->value==="Pending")->form([
                    Forms\Components\ToggleButtons::make('status')->required()->live()->default('Approved')->colors(['Approved' => 'success', 'Rejected' => 'danger'])->options(['Approved' => 'Approve', 'Pending' => "Pending", 'Rejected' => 'Reject'])->grouped(),
                    Forms\Components\Textarea::make('comment')->maxLength(255)
                ])->requiresConfirmation()->action(function ($record, $data) {
                    $record->update(['receive_status' => $data['status'], 'receive_comment' => $data['comment']]);
                })->color('success')->icon('heroicon-m-check-badge')->modalIcon('heroicon-m-check-badge')->iconSize(IconSize::Medium)
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('receive')->form([
                    Forms\Components\ToggleButtons::make('status')->required()->live()->default('Approved')->colors(['Approved' => 'success', 'Rejected' => 'danger'])->options(['Approved' => 'Approve', 'Pending' => "Pending", 'Rejected' => 'Reject'])->grouped(),
                    Forms\Components\Textarea::make('comment')->maxLength(255)
                ])->requiresConfirmation()->action(function ($records, $data) {
                    $records->each(function ($record) use ($data) {
                        $record->update([
                            'receive_status' => $data['status'],
                            'receive_comment' => $data['comment'],
                        ]);
                    });
                    sendSuccessNotification();
                })->visible(fn()=>$this->record->status->value==="Pending")->color('success')->icon('heroicon-m-check-badge')->modalIcon('heroicon-m-check-badge')->iconSize(IconSize::Medium)
            ]);
    }
}
