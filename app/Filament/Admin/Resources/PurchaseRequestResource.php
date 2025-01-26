<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PurchaseRequestResource\Pages;
use App\Filament\Admin\Resources\PurchaseRequestResource\RelationManagers;
use App\Models\Bid;
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
use Filament\Notifications\Notification;
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

    protected static ?string $pluralLabel = 'Purchase Request';
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
                        ->label('PR Number')->default(function (){
                           $puncher= PurchaseRequest::query()->where('company_id',getCompany()->id)->latest()->first();
                           if ($puncher){
                               return  generateNextCodePO($puncher->purchase_number);
                           }else{
                               return "0001";
                           }
                        })
                        ->unique(ignoreRecord: true, modifyRuleUsing: function (Unique $rule) {
                            return $rule->where('company_id', getCompany()->id);
                        })
                        ->required()
                        ->numeric(),

                    Forms\Components\DatePicker::make('request_date')->default(now())->label('Request Date')->required(),
                    Forms\Components\Hidden::make('status')->label('Status')->default('Requested')->required(),
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
                                ->content(fn($state, Get $get) => number_format((((int)str_replace(',', '', $get('quantity'))) * ((int)str_replace(',', '', $get('estimated_unit_cost')))))),

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
                Tables\Columns\TextColumn::make('request_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('employee.fullName')->searchable(),
                Tables\Columns\TextColumn::make('department')->state(fn($record) => $record->employee->department->title)->numeric()->sortable(),
                Tables\Columns\TextColumn::make('location')->state(fn($record) => $record->employee?->structure?->title)->numeric()->sortable(),
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


                    return [
                        Section::make([
                            Forms\Components\DatePicker::make('opening_date')->default(now())->required(),
                            Select::make('currency')->options(getCurrency())->searchable()->preload()->required(),

                            Placeholder::make('content')->content(function () use ($record) {
                                $trs = "";
                                $totalTrs = "
                                <tr>
                                        <td style='border: 1px solid black;padding: 8px;text-align: center'> </td>
                                        <td style='border: 1px solid black;padding: 8px;text-align: center'> </td>
                                        <td style='border: 1px solid black;padding: 8px;text-align: center'> </td>
                                        <td style='border: 1px solid black;padding: 8px;text-align: center'> </td>
                                ";
                                $vendors = '';
                                $ths = '';
                                foreach ($record->quotations as $quotation) {
                                    $vendor = $quotation->party->name;
                                    $vendors .= "<th style='border: 1px solid black;padding: 8px;text-align: center;background-color: #f2f2f2'>{$vendor}</th>";
                                    $ths .= "<th style='border: 1px solid black;padding: 8px;text-align: center;background-color: #f2f2f2'>Unit Cost | Total Cost</th>";
                                    $totalSum = 0;
                                    foreach ($quotation->quotationItems as $quotationItem) {
                                        $totalSum += $quotationItem->item->quantity * $quotationItem->unit_rate;
                                    }
                                    $totalSum = number_format($totalSum);
                                    $totalTrs .= "<td style='border: 1px solid black;padding: 8px;text-align: center'> {$totalSum}</td>";
                                }
                                $totalTrs .= "<td style='border: 1px solid black;padding: 8px;text-align: center'> </td></tr>";
                                foreach ($record->items->where('status', 'purchased') as $item) {
                                    $product = $item->product->title . " (" . $item->product->sku . ")";
                                    $description = $item->description;
                                    $quantity = $item->quantity;
                                    $tr = "<tr>
                                                 <td style='border: 1px solid black;padding: 8px;text-align: center'>$product</td>
                                                 <td style='border: 1px solid black;padding: 8px;text-align: center'>$description</td>
                                                 <td style='border: 1px solid black;padding: 8px;text-align: center'>{$item->unit->title}</td>
                                                 <td style='border: 1px solid black;padding: 8px;text-align: center'>$quantity</td>

                                             ";
                                    foreach ($item->quotationItems as $quotationItem) {
                                        $total = number_format($quotationItem->item->quantity * $quotationItem->unit_rate);
                                        $rate = number_format($quotationItem->unit_rate);
                                        $tr .= "<td style='border: 1px solid black;padding: 8px;text-align: center'>{$rate} | {$total}</td>";
                                    }
                                    $tr .= "<td style='border: 1px solid black;padding: 8px;text-align: center'>AFS</td>";
                                    $tr .= "</tr>";
                                    $trs .= $tr;
                                }

                                $table = "
<table style='border-collapse: collapse;width: 100%'>
    <thead>
        <tr>
            <th style='border: 1px solid black;padding: 8px;text-align: center;background-color: #f2f2f2'>Item</th>
            <th style='border: 1px solid black;padding: 8px;text-align: center;background-color: #f2f2f2'>Item Description</th>
            <th style='border: 1px solid black;padding: 8px;text-align: center;background-color: #f2f2f2'>Unit</th>
            <th style='border: 1px solid black;padding: 8px;text-align: center;background-color: #f2f2f2'>Qty</th>
            $vendors
            <th style='border: 1px solid black;padding: 8px;text-align: center;background-color: #f2f2f2'>Remarks</th>
        </tr>
        <tr>
            <th style='border: 1px solid black;padding: 8px;text-align: center;background-color: #f2f2f2'></th>
            <th style='border: 1px solid black;padding: 8px;text-align: center;background-color: #f2f2f2'></th>
            <th style='border: 1px solid black;padding: 8px;text-align: center;background-color: #f2f2f2'></th>
            <th style='border: 1px solid black;padding: 8px;text-align: center;background-color: #f2f2f2'></th>
          $ths
            <th style='border: 1px solid black;padding: 8px;text-align: center;background-color: #f2f2f2'></th>
        </tr>
    </thead>
    <tbody>
        {$trs}
        $totalTrs
    </tbody>
</table>";
                                return new HtmlString($table);
                            })->columnSpanFull(),
                            Select::make('quotation_id')->options(function () use ($record) {
                                $data = [];
                                $quotations = Quotation::query()->where('purchase_request_id', $record->id)->get();
                                foreach ($quotations as $quotation) {
                                    $data[$quotation->id] = $quotation->party->name;
                                }
                                return $data;

                            })->required()->label('Quotation Selected')->preload()->searchable()->columnSpanFull(),
                            Select::make('position_procurement_controller')->multiple()->options(Employee::query()->where('company_id', getCompany()->id)->pluck('fullName', 'id'))->preload()->searchable(),
                            Select::make('procurement_committee_members')->multiple()->options(Employee::query()->where('company_id', getCompany()->id)->pluck('fullName', 'id'))->preload()->searchable()
                        ])->columns(2)
                    ];
                })->action(function ($data, $record) {
                    $data['company_id'] = getCompany()->id;
                    $data['purchase_request_id'] = $record->id;
                    $quotation = Quotation::query()->firstWhere('id', $data['quotation_id']);
                    $totalSum = 0;
                    foreach ($quotation->quotationItems as $quotationItem) {
                        $totalSum += $quotationItem->item->quantity * $quotationItem->unit_rate;
                    }
                    $data['total_cost'] = $totalSum;
                    Bid::query()->create($data);
                    Notification::make('make bid')->success()->title('Created Successfully')->send()->sendToDatabase(auth()->user());
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
