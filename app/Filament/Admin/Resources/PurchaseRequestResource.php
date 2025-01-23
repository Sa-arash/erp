<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PurchaseRequestResource\Pages;
use App\Filament\Admin\Resources\PurchaseRequestResource\RelationManagers;
use App\Models\Employee;
use App\Models\PurchaseRequest;
use App\Models\Quotation;
use App\Models\Structure;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconSize;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rules\Unique;
use Nette\Utils\Html;

class PurchaseRequestResource extends Resource
{
    protected static ?string $model = PurchaseRequest::class;
    protected static ?string $modelLabel = 'Request';
    protected static ?string $navigationGroup = 'Stock Management';

    protected static ?string $navigationIcon = 'heroicon-c-document-arrow-down';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('')->schema([
                    Forms\Components\Select::make('employee_id')->live()
                        ->searchable()
                        ->preload()
                        ->label('Requested By')
                        ->required()
                        ->options(getCompany()->employees->pluck('fullName', 'id'))
                        ->default(fn() => auth()->user()->employee->id),

                    Forms\Components\TextInput::make('purchase_number')
                        ->label('PR Number')
                        ->unique(ignoreRecord: true, modifyRuleUsing: function (Unique $rule) {
                            return $rule->where('company_id', getCompany()->id);
                        })
                        ->required()
                        ->numeric(),

                    Forms\Components\DatePicker::make('request_date')
                        ->default(now())
                        ->label('Request Date')
                        ->required(),


                    Forms\Components\Hidden::make('status')
                        ->label('Status')
                        ->default('Requested')
                        ->required(),


                    Forms\Components\TextInput::make('description')
                        ->label('Description'),

                    Forms\Components\Hidden::make('company_id')
                        ->default(Filament::getTenant()->id)
                        ->required(),

                    Repeater::make('Requested Items')
                        ->relationship('items')
                        ->schema([
                            Forms\Components\Select::make('product_id')
                                ->label('Product')->options(function () {
                                    $products = getCompany()->products;
                                    $data = [];
                                    foreach ($products as $product) {
                                        $data[$product->id] = $product->title . " (" . $product->sku . ")";
                                    }
                                    return $data;
                                })->required()->searchable()->preload(),

                            Forms\Components\TextInput::make('description')
                                ->label('Description')
                                ->required(),

                            Forms\Components\Select::make('unit_id')
                                ->searchable()
                                ->preload()
                                ->label('Unit')
                                ->options(getCompany()->units->pluck('title', 'id'))
                                ->required(),
                            Forms\Components\TextInput::make('quantity')
                                ->required()->live()
                                ->mask(RawJs::make('$money($input)'))
                                ->stripCharacters(','),

                            Forms\Components\TextInput::make('estimated_unit_cost')
                                ->label('Estimated Unit Cost')->live()
                                ->numeric()
                                ->mask(RawJs::make('$money($input)'))
                                ->stripCharacters(','),

                            Forms\Components\Select::make('project_id')
                                ->searchable()
                                ->preload()
                                ->label('Project')
                                ->options(getCompany()->projects->pluck('name', 'id')),

                            Placeholder::make('total')
                                ->content(fn($state, Get $get) => (((int)str_replace(',', '', $get('quantity'))) * ((int)str_replace(',', '', $get('estimated_unit_cost'))))),

                            Forms\Components\Hidden::make('company_id')
                                ->default(Filament::getTenant()->id)
                                ->required(),
                        ])
                        ->columns(7)
                        ->columnSpanFull(),
                    // Section::make('estimated_unit_cost')->schema([
                    //     Placeholder::make('Total')->live()
                    //     ->content(function (Get $get) {
                    //         $sum = 0;
                    //         foreach($get('Requested Items') as $item)
                    //         {
                    //             $sum += (int)$item['quantity']*(int)$item['estimated_unit_cost'];
                    //         }
                    //         return $sum;
                    //     } )
                    // ])
                ])->columns(2)


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('purchase_number')->label('PR NO')->searchable(),

                Tables\Columns\TextColumn::make('request_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('employee.fullName')
                    ->searchable(),
                Tables\Columns\TextColumn::make('department')
                    ->state(fn($record) => $record->employee->department->title)
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location')
                    ->state(fn($record) => $record->employee->structure->title)
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('total')
                    ->state(function ($record) {
                        $total = 0;
                        foreach ($record->items as $item) {
                            $total += $item->quantity * $item->estimated_unit_cost;
                        }
                        return $total;
                    })->numeric(),
                Tables\Columns\TextColumn::make('warehouse_decision')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('warehouse_status_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('department_manager_status_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('ceo_status_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('purchase_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),


            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('bid')->form(function ($record) {

                    foreach ($record->quotations as $quotation) {

                    }
                    return [
                        Section::make([
                            Forms\Components\DatePicker::make('opening_date')->default(now())->required(),
                            Select::make('currency')->options(getCurrency())->searchable()->preload()->required(),

                            Placeholder::make('content')->content(function () use ($record) {
                                $trs = "";
                                $vendors = '';
                                $ths = '';
                                foreach ($record->quotations as $quotation) {
                                    $vendor = $quotation->party->name;
                                    $vendors .= "<th>{$vendor}</th>";
                                    $ths .= "<th>Unit Cost | Total Cost</th>";
                                }

                                foreach ($record->items->whereIn('ceo_decision', ['purchase', 'approve']) as $item) {
                                    $description = $item->description;
                                    $quantity = $item->quantity;
                                    $tr = "<tr>
                                                 <td>$description</td>
                                                 <td>{$item->unit->title}</td>
                                                 <td>$quantity</td>

                                             ";
                                    foreach ($item->quotationItems as $quotationItem) {
                                        $total = number_format($quotationItem->item->quantity * $quotationItem->unit_rate);
                                        $rate = number_format($quotationItem->unit_rate);
                                        $tr .= "<td>{$rate} | {$total}</td>";
                                    }
                                    $tr .= "<td>AFS</td>";
                                    $tr .= "</tr>";
                                    $trs .= $tr;
                                }
                                $table = "
<style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>

<table>
    <thead>
        <tr>
            <th>Item Description</th>
            <th>Unit</th>
            <th>Qty</th>
            $vendors
            <th>Remarks</th>
        </tr>
        <tr>
            <th></th>
            <th></th>
            <th></th>
          $ths
            <th></th>
        </tr>
    </thead>
    <tbody>
        {$trs}
    </tbody>
</table>";
                                return new HtmlString($table);
                            })->columnSpanFull(),
                                Select::make('quotation_id')->options(function ()use($record){
                                    $data=[];
                                    $quotations= Quotation::query()->where('purchase_request_id',$record->id)->get();
                                    foreach ($quotations as $quotation){
                                        $data[$quotation->id]=$quotation->party->name;
                                    }
                                    return $data;

                                })->required()->label('Quotation Selected')->preload()->searchable()->columnSpanFull(),
                                Select::make('position_procurement_controller')->options(Employee::query()->where('company_id',getCompany()->id)->pluck('fullName','id'))->preload()->searchable(),
                                Select::make('procurement_committee_members')->options(Employee::query()->where('company_id',getCompany()->id)->pluck('fullName','id'))->preload()->searchable()
                        ])->columns(2)
                    ];
                })->modalWidth(MaxWidth::Full),
                Tables\Actions\Action::make('prPDF')->label('PR PDF')->iconSize(IconSize::Large)->icon('heroicon-s-printer')->url(fn($record) => route('pdf.purchase', ['id' => $record->id])),
                Tables\Actions\Action::make('prQuotation')->color('warning')->label('Qu PDF')->iconSize(IconSize::Large)->icon('heroicon-s-printer')->url(fn($record) => route('pdf.quotation', ['id' => $record->id])),
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
//                Tables\Actions\Action::make('Watehouse Check')->modalWidth(MaxWidth::Full)
//
//                    ->form(function ($record) {
//
//                        return [
//                            Repeater::make('items')->formatStateUsing(fn($record) => $record->items->toArray())
//                                ->schema([
//                                    Forms\Components\Select::make('product_id')
//                                        ->searchable()
//                                        ->preload()
//                                        ->label('Product')
//                                        ->options(getCompany()->products->pluck('title', 'id'))
//                                        ->required(),
//
//                                    Forms\Components\TextInput::make('description')
//                                        ->label('Description')
//                                        ->required(),
//
//                                    Forms\Components\Select::make('unit_id')
//                                        ->searchable()
//                                        ->preload()
//                                        ->label('Unit')
//                                        ->options(getCompany()->units->pluck('title', 'id'))
//                                        ->required(),
//                                    Forms\Components\TextInput::make('quantity')
//                                        ->required()
//                                        ->mask(RawJs::make('$money($input)'))
//                                        ->stripCharacters(','),
//
//                                    Forms\Components\TextInput::make('estimated_unit_cost')
//                                        ->label('Estimated Unit Cost')
//                                        ->numeric()
//                                        ->mask(RawJs::make('$money($input)'))
//                                        ->stripCharacters(','),
//
//                                    Forms\Components\Select::make('project_id')
//                                        ->searchable()
//                                        ->preload()
//                                        ->label('Project')
//                                        ->options(getCompany()->projects->pluck('name', 'id')),
//
//
//                                    Forms\Components\Select::make('warehouse_decision')
//                                        ->label('Warehouse Decision')
//                                        ->options([
//                                            'available_in_stock' => 'Available in Stock',
//                                            'needs_purchase' => 'Needs Purchase',
//                                        ])
//                                        ->default('needs_purchase')
//                                        ->required(),
//
//                                    // Forms\Components\Select::make('status')
//                                    //     ->label('Status')
//                                    //     ->options([
//                                    //         'purchased' => 'Purchased',
//                                    //         'assigned' => 'Assigned',
//                                    //         'not_purchased' => 'Not Purchased',
//                                    //         'rejected' => 'Rejected',
//                                    //     ])
//                                    //     ->default('not_purchased')
//                                    //     ->required(),
//
//
//                                    Forms\Components\Hidden::make('company_id')
//                                        ->default(Filament::getTenant()->id)
//                                        ->required(),
//                                ])->columns(7)
//                        ];
//                    })->action(function (array $data,   $record): void {
//                      $record->items->delete();
//                            $record->items->create($data['items']);
//
//
//
//                    })
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemsRelationManager::class,
            RelationManagers\QuotationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchaseRequests::route('/'),
            'create' => Pages\CreatePurchaseRequest::route('/create'),
            'edit' => Pages\EditPurchaseRequest::route('/{record}/edit'),
            'view' => Pages\ViewPurcheseRequest::route('/{record}/view'),
        ];
    }
}
