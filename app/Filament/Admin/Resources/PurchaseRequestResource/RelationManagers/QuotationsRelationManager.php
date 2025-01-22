<?php

namespace App\Filament\Admin\Resources\PurchaseRequestResource\RelationManagers;

use App\Models\Employee;
use App\Models\Parties;
use App\Models\Quotation;
use App\Models\QuotationItem;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class QuotationsRelationManager extends RelationManager
{
    protected static string $relationship = 'quotations';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('party_id')->label('Vendor')->options(Parties::query()->where('company_id', getCompany()->id)->pluck('name', 'id'))->searchable()->preload()->required(),
                Forms\Components\DatePicker::make('date')->default(now())->required(),
                Forms\Components\Select::make('employee_id')->required()->options(Employee::query()->where('company_id', getCompany()->id)->pluck('fullName', 'id'))->searchable()->preload()->label('Logistic'),
                Forms\Components\Select::make('employee_operation_id')->required()->options(Employee::query()->where('company_id', getCompany()->id)->pluck('fullName', 'id'))->searchable()->preload()->label('Operation'),
                Forms\Components\FileUpload::make('file')->downloadable()->columnSpanFull(),
                Repeater::make('Requested Items')
                    ->schema([
                        Forms\Components\Select::make('purchase_request_item_id')->disableOptionsWhenSelectedInSiblingRepeaterItems()
                            ->label('Product')->options(function () {
                                $products = $this->ownerRecord->items->whereIn('ceo_decision', ['purchase', 'approve']);
                                $data = [];
                                foreach ($products as $product) {
                                    $data[$product->id] = $product->product->title . " (" . $product->product->sku . ")";
                                }
                                return $data;
                            })->required()->searchable()->preload(),
                        Forms\Components\TextInput::make('unit_rate')->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                            if ($get('quantity') and $get('unit_rate')) {
                                $set('total', number_format(str_replace(',', '', $get('unit_rate')) * $get('quantity')));;
                            }
                        })->live()->required()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
                        Forms\Components\TextInput::make('quantity')->readOnly()->live()->required()->numeric(),
                        Forms\Components\TextInput::make('total')->readOnly()->required()->mask(RawJs::make('$money($input)'))->stripCharacters(','),

                    ])->formatStateUsing(function () {
                        $data = [];
                        foreach ($this->ownerRecord->items->whereIn('ceo_decision', ['purchase', 'approve']) as $item) {
                            $data[] = ['purchase_request_item_id' => $item->id, 'quantity' => $item->quantity, 'unit_rate' => 0];
                        }
                        return $data;
                    })
                    ->columns(4)->columnSpanFull()

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('party.name')->label('Vendor'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->action(function ($data) {

                    $id = getCompany()->id;
                    $quotation= Quotation::query()->create([
                        'purchase_request_id' => $this->ownerRecord->id,
                        'party_id' => $data['party_id'],
                        'date' => $data['date'],
                        'employee_id' => $data['employee_id'],
                        'employee_operation_id' => $data['employee_operation_id'],
                        'company_id' => $id,
                    ]);

                    foreach ($data['Requested Items'] as $item) {
                        $quotation->quotationItems()->create([
                            'purchase_request_item_id'=>$item['purchase_request_item_id'],
                            'unit_rate'=>$item['unit_rate'],
                            'date'=>$data['date'],
                            'company_id'=>$id
                        ]);
                    }
                    Notification::make('add quotation')->success()->title('Quotation Added')->send()->sendToDatabase(auth()->user());

                }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
