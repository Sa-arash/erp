<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PurchaseRequestResource\Pages;
use App\Filament\Admin\Resources\PurchaseRequestResource\RelationManagers;
use App\Models\Account;
use App\Models\Bid;
use App\Models\Employee;
use App\Models\Parties;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\Quotation;
use App\Models\Structure;
use Closure;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
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
                        ->label('PR Number')->default(function () {
                            $puncher = PurchaseRequest::query()->where('company_id', getCompany()->id)->latest()->first();
                            if ($puncher) {

                                return  generateNextCodePO($puncher->purchase_number);

                            } else {
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
                        ->label('Description')->columnSpanFull(),

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
                                        $data[$product->id] = $product->title . " (sku:" . $product->sku . ")";
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
                ])->columns(3)


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('purchase_number')->label('PR NO')->searchable(),
                Tables\Columns\TextColumn::make('request_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('employee.fullName')->searchable(),
                Tables\Columns\TextColumn::make('department')->state(fn($record) => $record->employee->department->title)->numeric()->sortable(),
                Tables\Columns\TextColumn::make('location')->state(fn($record) => $record->employee?->structure?->title)->numeric()->sortable(),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('total')
                    ->label('Total(' . getCompany()->currency . ")")
                    ->state(function ($record) {
                        $total = 0;
                        foreach ($record->items as $item) {
                            $total += $item->quantity * $item->estimated_unit_cost;
                        }
                        return $total;
                    })->numeric(),
                Tables\Columns\TextColumn::make('warehouse_decision')->date()->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('warehouse_status_date')->date()->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('department_manager_status_date')->date()->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('ceo_status_date')->date()->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('purchase_date')->date()->sortable()->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('create po')
                    ->modalWidth(MaxWidth::FitContent)->form([
                        Section::make('Payment')->schema([
                            Forms\Components\Select::make('account_id')
                                ->label('Payment Account')
                                ->options(
                                    function () {
                                        $accounts = getCompany()->accounts->whereIn('stamp', ['Bank', 'Cash'])->pluck('id')->toArray();
                                        return Account::query()->whereIn('id', $accounts)
                                            ->orWhereIn('parent_id', $accounts)
                                            ->orWhereHas('account', function ($query) use ($accounts) {
                                                return $query->whereIn('parent_id', $accounts)->orWhereHas('account', function ($query) use ($accounts) {
                                                    return $query->whereIn('parent_id', $accounts);
                                                });
                                            })
                                            ->get()->pluck('name', 'id')->toArray();
                                    }



                                )
                                ->searchable()
                                ->preload()
                                ->required(),

                            Forms\Components\Select::make('vendor_id')->label('Vendor')

                                ->options(getCompany()->parties->where('type', 'vendor')->map(fn($item) => $item->name . "(" . $item->accountVendor->code . ")")->toArray())

                                ->searchable()
                                ->preload()
                                ->required(),
                            Forms\Components\Select::make('currency')->required()->required()->options(getCurrency())->searchable()->preload()->default(getCompany()->currency),
                            Forms\Components\TextInput::make('exchange_rate')
                                ->required()->default(1)
                                ->numeric(),



                        ])->columns(4),
                        Section::make('')->schema([
                            Forms\Components\Select::make('prepared_by')->live()
                                ->searchable()
                                ->preload()
                                ->required()
                                ->options(getCompany()->employees->pluck('fullName', 'id'))
                                ->default(fn() => auth()->user()->employee->id),

                            Forms\Components\TextInput::make('purchase_orders_number')->default(function () {
                                $puncher = PurchaseOrder::query()->where('company_id', getCompany()->id)->latest()->first();
                                if ($puncher) {
                                    return  generateNextCodePO($puncher->purchase_orders_number);
                                } else {
                                    return "0001";
                                }
                            })->label('Po Number')
                                ->required()
                                ->unique(ignoreRecord: true, modifyRuleUsing: function (Unique $rule) {
                                    return $rule->where('company_id', getCompany()->id);
                                })
                                ->maxLength(50),



                            Forms\Components\DatePicker::make('date_of_delivery')
                                ->default(now())
                                ->required(),
                            Forms\Components\TextInput::make('location_of_delivery')

                                ->maxLength(255),



                            Forms\Components\DatePicker::make('date_of_po')->default(now())
                                ->label('Date of PO')
                                ->required(),
                            // Forms\Components\Select::make('bid_id')
                            // ->relationship('bid', 'id')
                            // ->required(),
                            // Forms\Components\Select::make('quotation_id')
                            // ->relationship('quotation', 'id')
                            // ->required(),
                            Hidden::make('purchase_request_id'),


                            Forms\Components\Hidden::make('company_id')
                                ->default(getCompany()->id)
                                ->required(),



                            Repeater::make('RequestedItems')->defaultItems(0)->required()
                                ->default(fn($record) => PurchaseRequestItem::where('purchase_request_id', $record->purchase_number)->get()->map(function ($item) {
                                    $item->taxes = $item->freights = $item->unit_price = 0;
                                    return $item;
                                })->toArray())
                                // ->formatStateUsing(fn(Get $get) => dd($get('purchase_request_id')):'')
                                ->relationship('items')
                                ->schema([
                                    Forms\Components\Select::make('product_id')
                                        ->disabled()
                                       ,

                                    Forms\Components\TextInput::make('description')
                                        ->disabled()
                                        ->label('Description')
                                        ->required(),

                                    Forms\Components\Select::make('unit_id')
                                        ->disabled()
                                        ->searchable()
                                        ->preload()
                                        ->label('Unit')
                                        ->options(getCompany()->units->pluck('title', 'id'))
                                        ->required(),
                                    Forms\Components\TextInput::make('quantity')
                                        ->disabled()
                                        ->required()->live()
                                        ->mask(RawJs::make('$money($input)'))
                                        ->stripCharacters(','),

                                    Forms\Components\TextInput::make('estimated_unit_cost')
                                        ->required()
                                        ->disabled()
                                        ->numeric()
                                        ->mask(RawJs::make('$money($input)'))
                                        ->stripCharacters(','),
                                    Forms\Components\TextInput::make('unit_price')
                                        ->required()
                                        ->label('Unit Cost')
                                        ->numeric()
                                        ->mask(RawJs::make('$money($input)'))
                                        ->stripCharacters(','),

                                    Forms\Components\TextInput::make('taxes')
                                        ->prefix('%')
                                        ->numeric()
                                        ->required()
                                        ->rules([
                                            fn(): Closure => function (string $attribute, $value, Closure $fail) {
                                                if ($value < 0) {
                                                    $fail('The :attribute must be greater than 0.');
                                                }
                                                if ($value > 100) {
                                                    $fail('The :attribute must be less than 100.');
                                                }
                                            },
                                        ])
                                        ->mask(RawJs::make('$money($input)'))
                                        ->stripCharacters(','),
                                    Forms\Components\TextInput::make('freights')
                                        ->required()
                                        ->numeric()
                                        ->mask(RawJs::make('$money($input)'))
                                        ->stripCharacters(','),

                                    Forms\Components\Select::make('project_id')
                                        ->searchable()
                                        ->preload()
                                        ->disabled()
                                        ->label('Project')
                                        ->options(getCompany()->projects->pluck('name', 'id')),

                                    Placeholder::make('total')
                                        ->content(fn($state, Get $get) => number_format((((int)str_replace(',', '', $get('quantity'))) * ((int)str_replace(',', '', $get('estimated_unit_cost')))))),

                                    Forms\Components\Hidden::make('company_id')
                                        ->default(Filament::getTenant()->id)
                                        ->required(),
                                ])
                                ->columns(10)
                                ->columnSpanFull(),
                        ])->columns(3)
                    ])->action(function ($data, $record) {

                        dd($data);
                        // $id = getCompany()->id;
                        // $quotation= Quotation::query()->create([
                        //     'purchase_request_id' => $record->id,
                        //     'party_id' => $data['party_id'],
                        //     'date' => $data['date'],
                        //     'employee_id' => $data['employee_id'],
                        //     'employee_operation_id' => $data['employee_operation_id'],
                        //     'company_id' => $id,
                        // ]);

                        // foreach ($data['Requested Items'] as $item) {
                        //     $quotation->quotationItems()->create([
                        //         'purchase_request_item_id'=>$item['purchase_request_item_id'],
                        //         'unit_rate'=>$item['unit_rate'],
                        //         'date'=>$data['date'],
                        //         'company_id'=>$id
                        //     ]);
                        // }
                        // Notification::make('add quotation')->success()->title('Quotation Added')->send()->sendToDatabase(auth()->user());

                    }),












                Tables\Actions\ActionGroup::make([

                    Tables\Actions\Action::make('prQuotation')->visible(fn($record) => $record->is_quotation)->color('warning')->label('Qu ')->iconSize(IconSize::Large)->icon('heroicon-s-printer')->url(fn($record) => route('pdf.quotation', ['id' => $record->id])),


                    Tables\Actions\Action::make('insertQu')->modalWidth(MaxWidth::Full)->icon('heroicon-s-newspaper')->label('InsertQu')->visible(fn($record) => $record->is_quotation)->form(function ($record) {

                        return [
                            Section::make([
                                Forms\Components\Select::make('party_id')->label('Vendor')->options(Parties::query()->where('company_id', getCompany()->id)->pluck('name', 'id'))->searchable()->preload()->required(),
                                Forms\Components\DatePicker::make('date')->default(now())->required(),
                                Forms\Components\Select::make('employee_id')->required()->options(Employee::query()->where('company_id', getCompany()->id)->pluck('fullName', 'id'))->searchable()->preload()->label('Logistic'),
                                Forms\Components\Select::make('employee_operation_id')->required()->options(Employee::query()->where('company_id', getCompany()->id)->pluck('fullName', 'id'))->searchable()->preload()->label('Operation'),
                                Forms\Components\FileUpload::make('file')->downloadable()->columnSpanFull(),
                            ])->columns(4),
                            Repeater::make('Requested Items')->required()
                                ->schema([
                                    Forms\Components\Select::make('purchase_request_item_id')->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                        ->label('Product')->options(function () use ($record) {
                                            $products = $record->items->where('status', 'purchase');
                                            $data = [];
                                            foreach ($products as $product) {
                                                $data[$product->id] = $product->product->title . " (" . $product->product->sku . ")";
                                            }
                                            return $data;
                                        })->required()->searchable()->preload(),
                                    Forms\Components\TextInput::make('quantity')->readOnly()->live()->required()->numeric(),
                                    Forms\Components\TextInput::make('unit_rate')->afterStateUpdated(function (Forms\Get $get, Forms\Set $set,$state) {
                                        if ($get('quantity') and $get('unit_rate')) {
                                            $freights = $get('freights') === null ? 0 : (float)$get('freights');
                                            $q=$get('quantity');
                                            $tax=$get('taxes') === null ? 0 : (float)$get('taxes');
                                            $price= $state !==null? str_replace(',', '', $state): 0;
                                            $set('total', number_format(($q * $price) + ($q * $price * $tax)+ ($q * $price * $freights)));                                        }
                                    })->live(true)->required()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
                                    Forms\Components\TextInput::make('taxes')->afterStateUpdated(function ($state, Get $get, Forms\Set $set) {
                                        $freights = $get('freights') === null ? 0 : (float)$get('freights');
                                        $q=$get('quantity');
                                        $tax=$state === null ? 0 : (float)$state;
                                        $price= $get('unit_rate') !==null? str_replace(',', '', $get('unit_rate')): 0;
                                        $set('total', number_format(($q * $price) + ($q * $price * $tax)+ ($q * $price * $freights)));
                                    })->live(true)
                                        ->prefix('%')
                                        ->numeric()->maxValue(1)
                                        ->required()
                                        ->rules([
                                            fn(): \Closure => function (string $attribute, $value, \Closure $fail) {
                                                if ($value < 0) {
                                                    $fail('The :attribute must be greater than 0.');
                                                }
                                                if ($value > 1) {
                                                    $fail('The :attribute must be less than 100.');
                                                }
                                            },
                                        ])
                                        ->mask(RawJs::make('$money($input)'))
                                        ->stripCharacters(','),
                                    Forms\Components\TextInput::make('freights')->afterStateUpdated(function ($state, Get $get, Forms\Set $set){
                                        $tax = $get('taxes') === null ? 0 : (float)$get('taxes');
                                        $q=$get('quantity');
                                        $freights=$state === null ? 0 : (float)$state;
                                        $price= $get('unit_rate') !==null? str_replace(',', '', $get('unit_rate')): 0;
                                        $set('total', number_format(($q * $price) + ($q * $price * $tax)+ ($q * $price * $freights)));
                                    })->live(true)
                                        ->required()
                                        ->numeric()
                                        ->mask(RawJs::make('$money($input)'))
                                        ->stripCharacters(','),
                                    Forms\Components\TextInput::make('total')->readOnly()->required()->mask(RawJs::make('$money($input)'))->stripCharacters(','),

                                ])->formatStateUsing(function () use ($record) {
                                    $data = [];
                                    foreach ($record->items->where('status', 'purchase') as $item) {
                                        $data[] = ['purchase_request_item_id' => $item->id, 'quantity' => $item->quantity, 'unit_rate' => 0,'taxes'=>0,'freights'=>0];
                                    }
                                    return $data;
                                })
                                ->columns(6)->columnSpanFull()

                        ];
                    })->action(function ($data, $record) {

                        $id = getCompany()->id;
                        $quotation = Quotation::query()->create([
                            'purchase_request_id' => $record->id,
                            'party_id' => $data['party_id'],
                            'date' => $data['date'],
                            'employee_id' => $data['employee_id'],
                            'employee_operation_id' => $data['employee_operation_id'],
                            'company_id' => $id,
                        ]);

                        foreach ($data['Requested Items'] as $item) {
                            $quotation->quotationItems()->create([
                                'purchase_request_item_id' => $item['purchase_request_item_id'],
                                'unit_rate' => $item['unit_rate'],
                                'date' => $data['date'],

                                'freights' => $item['freights'],
                                'taxes' => $item['taxes'],

                                'company_id' => $id
                            ]);
                        }
                        Notification::make('add quotation')->success()->title('Quotation Added')->send()->sendToDatabase(auth()->user());
                    }),
                    Tables\Actions\Action::make('prPDF')->label('PR ')->iconSize(IconSize::Large)->icon('heroicon-s-printer')->url(fn($record) => route('pdf.purchase', ['id' => $record->id])),

                    Tables\Actions\Action::make('bid')->icon('heroicon-c-check-badge')->form(function ($record) {
                        return [
                            Section::make([
                                Forms\Components\DatePicker::make('opening_date')->default(now())->required(),
                                Select::make('quotation_id')->options(function () use ($record) {
                                    $data = [];
                                    $quotations = Quotation::query()->where('purchase_request_id', $record->id)->get();
                                    foreach ($quotations as $quotation) {
                                        $data[$quotation->id] = $quotation->party->name;
                                    }
                                    return $data;
                                })->required()->label('Quotation Selected')->preload()->searchable(),
                                Select::make('position_procurement_controller')->multiple()->options(Employee::query()->where('company_id', getCompany()->id)->pluck('fullName', 'id'))->preload()->searchable(),
                                Select::make('procurement_committee_members')->multiple()->options(Employee::query()->where('company_id', getCompany()->id)->pluck('fullName', 'id'))->preload()->searchable(),

                            ])->columns(4),
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
                                foreach ($record->items->where('status', 'purchase') as $item) {
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
                    })->modalWidth(MaxWidth::Full)->visible(fn($record) => $record->quotations->count() > 0),
                ]),
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
