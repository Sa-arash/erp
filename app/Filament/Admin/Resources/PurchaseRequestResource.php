<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PurchaseRequestResource\Pages;
use App\Filament\Admin\Resources\PurchaseRequestResource\RelationManagers;
use App\Models\Account;
use App\Models\Bid;
use App\Models\Employee;
use App\Models\Parties;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\Quotation;
use App\Models\Structure;
use App\Models\Transaction;
use App\Models\Unit;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rules\Unique;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use Nette\Utils\Html;

class PurchaseRequestResource extends Resource
{
    protected static ?string $model = PurchaseRequest::class;

    protected static ?int $navigationSort = 5;
    protected static ?string $pluralLabel = 'Purchase Request';
    protected static ?string $modelLabel = 'Request';
    protected static ?string $navigationGroup = 'Logistic Management';

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
                    getSelectCurrency(),
                    Forms\Components\TextInput::make('description')->label('Description')->columnSpanFull(),
                    Repeater::make('Requested Items')
                    ->addActionLabel('Add')
                        ->relationship('items')
                        ->schema([
                            Forms\Components\Select::make('product_id')
                                ->label('Product')->options(function () {
                                    return getCompany()->products->pluck('info', 'id');
                                })->required()->searchable()->preload(),
                            Forms\Components\Select::make('unit_id')->createOptionForm([
                                Forms\Components\TextInput::make('title')->label('Unit Name')->unique('units', 'title')->required()->maxLength(255),
                                Forms\Components\Toggle::make('is_package')->live()->required(),
                                Forms\Components\TextInput::make('items_per_package')->numeric()->visible(fn(Get $get) => $get('is_package'))->default(null),
                            ])->createOptionUsing(function ($data) {
                                $data['company_id'] = getCompany()->id;
                                Notification::make('success')->success()->title('Create Unit')->send();
                                return  Unit::query()->create($data)->getKey();
                            })->searchable()->preload()->label('Unit')->options(getCompany()->units->pluck('title', 'id'))->required(),
                            Forms\Components\TextInput::make('quantity')->required()->live()->mask(RawJs::make('$money($input)'))->stripCharacters(','),

                            Forms\Components\TextInput::make('estimated_unit_cost')
                                ->label('Estimated Unit Cost')->live(true)
                                ->numeric()->required()
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
                            Forms\Components\Textarea::make('description')
                                ->label(' Product Name And Description')
                                ->columnSpanFull()
                                ->required(),

                        ])
                        ->columns(6)
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
                ])->columns(4)


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('request_date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('purchase_number')->label('PR NO')->searchable(),
                Tables\Columns\TextColumn::make('request_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('employee.fullName')->searchable(),
                Tables\Columns\TextColumn::make('department')->state(fn($record) => $record->employee->department->title)->numeric()->sortable(),
                // Tables\Columns\TextColumn::make('location')->state(fn($record) => $record->employee?->structure?->title)->numeric()->sortable(),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('bid.quotation.party.name')->label('Vendor'),
                Tables\Columns\TextColumn::make('total')->label('Total Estimated')
                    ->label('Total(' . getCompany()->currency . ")")
                    ->state(function ($record) {
                        $total = 0;
                        foreach ($record->items as $item) {
                            $total += $item->quantity * $item->estimated_unit_cost;
                        }
                        return $total;
                    })->numeric(),
                Tables\Columns\TextColumn::make('bid.total_cost')->label('Total Price')->numeric(),

            ])

            ->filters([

                SelectFilter::make('purchase_number')->searchable()->preload()->options(PurchaseRequest::where('company_id', getCompany()->id)->get()->pluck('purchase_number', 'id'))
                    ->label("PR NO"),
                SelectFilter::make('vendor_id')->searchable()->preload()->options(Parties::where('company_id', getCompany()->id)->where('account_code_vendor', '!=', null)->get()->pluck('name', 'id'))
                    ->label("Vendor"),
                DateRangeFilter::make('request_date'),




            ], getModelFilter())
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('Order')
                    ->visible(fn($record) => $record->status->value == 'FinishedCeo')
                    ->icon('heroicon-s-shopping-cart')
                    ->url(fn($record) => PurchaseOrderResource::getUrl('create') . "?prno=" . $record->id),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('prQuotation')->visible(fn($record) => $record->is_quotation)->color('warning')->label('Quotation ')->iconSize(IconSize::Large)->icon('heroicon-s-printer')->url(fn($record) => route('pdf.quotation', ['id' => $record->id])),
                    Tables\Actions\Action::make('insertQuotation')->modalWidth(MaxWidth::Full)->icon('heroicon-s-newspaper')->color('info')->label('InsertQuotation')->visible(fn($record) => $record->is_quotation)->form(function ($record) {

                        return [
                            Section::make([
                                Forms\Components\Select::make('party_id')->createOptionUsing(function ($data) {
                                    $parentAccount = Account::query()->where('id', $data['parent_vendor'])->where('company_id', getCompany()->id)->first();
                                    $account = Account::query()->create([
                                        'name' =>  $data['name'],
                                        'type' => 'creditor',
                                        'code' => $parentAccount->code . $data['account_code_vendor'],
                                        'level' => 'detail',
                                        'parent_id' => $parentAccount->id,
                                        'built_in' => false,
                                        'company_id' => getCompany()->id,
                                    ]);
                                    $data['account_vendor'] = $account->id;
                                    $data['company_id'] = getCompany()->id;
                                    $data['type'] = 'vendor';
                                    return Parties::query()->create($data)->getKey();
                                })->createOptionForm([
                                    Forms\Components\Section::make([
                                        Forms\Components\TextInput::make('name')->label('Company/Name')->required()->maxLength(255),
                                        Forms\Components\TextInput::make('phone')->tel()->maxLength(255),
                                        Forms\Components\TextInput::make('email')->email()->maxLength(255),
                                        Forms\Components\Textarea::make('address')->columnSpanFull(),
                                        SelectTree::make('parent_vendor')->disabledOptions(function () {
                                            return Account::query()->where('level', 'detail')->where('company_id', getCompany()->id)->orWhereHas('transactions', function ($query) {})->pluck('id')->toArray();
                                        })->default(getCompany()?->vendor_account)->enableBranchNode()->model(Transaction::class)->defaultOpenLevel(3)->live()->label('Parent Vendor Account')->required()->relationship('Account', 'name', 'parent_id', modifyQueryUsing: fn($query) => $query->where('stamp', "Liabilities")->where('company_id', getCompany()->id)),
                                        Forms\Components\TextInput::make('account_code_vendor')->prefix(fn(Get $get) => Account::find($get('parent_vendor'))?->code)->default(function () {
                                            if (Parties::query()->where('company_id', getCompany()->id)->where('type', 'vendor')->latest()->first()) {
                                                return generateNextCode(Parties::query()->where('company_id', getCompany()->id)->where('type', 'vendor')->latest()->first()->account_code_vendor);
                                            } else {
                                                return "001";
                                            }
                                        })->required()->maxLength(255),
                                    ])->columns(3),
                                ])->label('Vendor')->options(Parties::query()->where('company_id', getCompany()->id)->where('type', 'vendor')->get()->pluck('info', 'id'))->searchable()->preload()->required(),
                                Forms\Components\DatePicker::make('date')->default(now())->required(),
                                Forms\Components\Select::make('employee_id')->required()->options(Employee::query()->where('company_id', getCompany()->id)->pluck('fullName', 'id'))->searchable()->preload()->label('Logistic'),
                                Forms\Components\Select::make('employee_operation_id')->required()->options(Employee::query()->where('company_id', getCompany()->id)->pluck('fullName', 'id'))->searchable()->preload()->label('Operation'),
                                Forms\Components\Select::make('currency')->required()->options(getCurrency())->searchable()->preload()->label('Currency'),
                                Forms\Components\FileUpload::make('file')->downloadable()->columnSpanFull(),
                                Forms\Components\Textarea::make('description')->columnSpanFull()->nullable()
                            ])->columns(5),
                            Repeater::make('Requested Items')->required()
                            ->addActionLabel('Add')
                                ->schema([
                                    Forms\Components\Select::make('purchase_request_item_id')->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                        ->label('Product')->options(function () use ($record) {
                                            $products = $record->items->where('status', 'approve');
                                            $data = [];
                                            foreach ($products as $product) {
                                                $data[$product->id] = $product->product->title . " (" . $product->product->sku . ")";
                                            }
                                            return $data;
                                        })->required()->searchable()->preload(),
                                    Forms\Components\TextInput::make('quantity')->readOnly()->live()->required()->numeric(),
                                    Forms\Components\TextInput::make('unit_rate')->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, $state) {
                                        if ($get('quantity') and $get('unit_rate')) {
                                            $freights = $get('freights') === null ? 0 : (float)$get('freights');
                                            $q = $get('quantity');
                                            $tax = $get('taxes') === null ? 0 : (float)$get('taxes');
                                            $price = $state !== null ? str_replace(',', '', $state) : 0;
                                            $set('total', number_format(($q * $price) + (($q * $price * $tax) / 100) + (($q * $price * $freights) / 100)));
                                        }
                                    })->live(true)->required()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
                                    Forms\Components\TextInput::make('taxes')->afterStateUpdated(function ($state, Get $get, Forms\Set $set) {
                                        $freights = $get('freights') === null ? 0 : (float)$get('freights');
                                        $q = $get('quantity');
                                        $tax = $state === null ? 0 : (float)$state;
                                        $price = $get('unit_rate') !== null ? str_replace(',', '', $get('unit_rate')) : 0;
                                        $set('total', number_format(($q * $price) + (($q * $price * $tax) / 100) + (($q * $price * $freights) / 100)));
                                    })->live(true)
                                        ->prefix('%')
                                        ->numeric()->maxValue(100)
                                        ->required()
                                        ->rules([
                                            fn(): \Closure => function (string $attribute, $value, \Closure $fail) {
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
                                    Forms\Components\TextInput::make('freights')->prefix('%')->afterStateUpdated(function ($state, Get $get, Forms\Set $set) {
                                        $tax = $get('taxes') === null ? 0 : (float)$get('taxes');
                                        $q = $get('quantity');
                                        $freights = $state === null ? 0 : (float)$state;
                                        $price = $get('unit_rate') !== null ? str_replace(',', '', $get('unit_rate')) : 0;
                                        $set('total', number_format(($q * $price) + (($q * $price * $tax) / 100) + (($q * $price * $freights) / 100)));
                                    })->live(true)
                                        ->required()
                                        ->numeric()
                                        ->mask(RawJs::make('$money($input)'))
                                        ->stripCharacters(','),
                                    Forms\Components\TextInput::make('total')->readOnly()->required()->mask(RawJs::make('$money($input)'))->stripCharacters(','),

                                    Forms\Components\Textarea::make('description')->columnSpanFull()->readOnly()->label('Product Name and Description'),

                                ])->formatStateUsing(function () use ($record) {
                                    $data = [];
                                    foreach ($record->items->where('status', 'approve') as $item) {
                                        $data[] = ['purchase_request_item_id' => $item->id, 'description' => $item->description, 'quantity' => $item->quantity, 'unit_rate' => 0, 'taxes' => 0, 'freights' => 0];
                                    }
                                    return $data;
                                })
                                ->columns(6)->addable(false)->columnSpanFull()

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
                            'currency' => $data['currency'],
                        ]);

                        foreach ($data['Requested Items'] as $item) {
                            $quotation->quotationItems()->create([
                                'purchase_request_item_id' => $item['purchase_request_item_id'],
                                'unit_rate' => $item['unit_rate'],
                                'date' => $data['date'],
                                'freights' => $item['freights'],
                                'taxes' => $item['taxes'],
                                'company_id' => $id,
                                'total' => $item['total']

                            ]);
                        }
                        Notification::make('add quotation')->success()->title('Quotation Added')->send()->sendToDatabase(auth()->user());
                    }),
                    Tables\Actions\Action::make('bid')->label('Bid Summery')->color('success')->icon('heroicon-c-check-badge')->form(function ($record) {
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
                                Select::make('position_procurement_controller')->label(' Procurement Controller')->multiple()->options(Employee::query()->where('company_id', getCompany()->id)->pluck('fullName', 'id'))->preload()->searchable(),
                                Select::make('procurement_committee_members')->label(' Committee Members')->multiple()->options(Employee::query()->where('company_id', getCompany()->id)->pluck('fullName', 'id'))->preload()->searchable(),

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
                                    $vendor = $quotation->party->name . "'s Quotation";
                                    $vendors .= "<th style='border: 1px solid black;padding: 8px;text-align: center;background-color:rgb(90, 86, 86)'>123123{$vendor}</th>";
                                    $totalSum = 0;

                                    foreach ($quotation->quotationItems as $quotationItem) {
                                        $totalSum += $quotationItem->total;
                                    }
                                    $totalSum = number_format($totalSum);
                                    $totalTrs .= "<td style='border: 1px solid black;padding: 8px;text-align: center'> Total {$totalSum}</td>";
                                }
                                $totalTrs .= "<td style='border: 1px solid black;padding: 8px;text-align: center'> </td></tr>";
                                foreach ($record->items->where('status', 'approve') as $item) {
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
                                        $tr .= "<td style='border: 1px solid black;padding: 8px;text-align: center'>  {$rate} Per Unit  |  {$total} Total</td>";
                                    }
                                    $tr .= "<td style='border: 1px solid black;padding: 8px;text-align: center'></td>";
                                    $tr .= "</tr>";
                                    $trs .= $tr;
                                }

                                $table = "
<table style='border-collapse: collapse;width: 100%'>
    <thead>
        <tr>
            <th style='border: 1px solid black;padding: 8px;text-align: center;background-color:rgb(90, 86, 86)'>Item</th>
            <th style='border: 1px solid black;padding: 8px;text-align: center;background-color:rgb(90, 86, 86)'>Item Description</th>
            <th style='border: 1px solid black;padding: 8px;text-align: center;background-color:rgb(90, 86, 86)'>Unit</th>
            <th style='border: 1px solid black;padding: 8px;text-align: center;background-color:rgb(90, 86, 86)'>Qty</th>
            $vendors
            <th style='border: 1px solid black;padding: 8px;text-align: center;background-color:rgb(90, 86, 86)'>Remarks</th>
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
                    Tables\Actions\Action::make('prPDF')->label('PR ')->iconSize(IconSize::Large)->icon('heroicon-s-printer')->url(fn($record) => route('pdf.purchase', ['id' => $record->id])),
                ]),


            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
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
