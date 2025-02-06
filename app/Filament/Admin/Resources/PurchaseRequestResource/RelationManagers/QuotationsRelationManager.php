<?php

namespace App\Filament\Admin\Resources\PurchaseRequestResource\RelationManagers;

use App\Models\Account;
use App\Models\Employee;
use App\Models\Parties;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Transaction;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class QuotationsRelationManager extends RelationManager
{
    protected static string $relationship = 'quotations';
    protected static ?string $label = 'Quotations';
    public function form(Form $form): Form
    {

        $record=$this->ownerRecord;
            return  $form->schema([
                \Filament\Forms\Components\Section::make([
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
                                return Account::query()->where('level', 'detail')->where('company_id', getCompany()->id)->orWhereHas('transactions',function ($query){})->pluck('id')->toArray();
                            })->default(getCompany()?->vendor_account)->enableBranchNode()->model(Transaction::class)->defaultOpenLevel(3)->live()->label('Parent Vendor Account')->required()->relationship('Account', 'name', 'parent_id', modifyQueryUsing: fn($query) => $query->where('stamp', "Liabilities")->where('company_id', getCompany()->id)),
                            Forms\Components\TextInput::make('account_code_vendor')->prefix(fn(Get $get) => Account::find($get('parent_vendor'))?->code)->default(function () {
                                if (Parties::query()->where('company_id', getCompany()->id)->where('type', 'vendor')->latest()->first()) {
                                    return generateNextCode(Parties::query()->where('company_id', getCompany()->id)->where('type', 'vendor')->latest()->first()->account_code_vendor);
                                } else {
                                    return "001";
                                }
                            })->required()->maxLength(255),
                        ])->columns(3),
                    ])->label('Vendor')->options(Parties::query()->where('company_id', getCompany()->id)->where('type','vendor')->get()->pluck('info', 'id'))->searchable()->preload()->required(),
                    Forms\Components\DatePicker::make('date')->default(now())->required(),
                    Forms\Components\Select::make('employee_id')->required()->options(Employee::query()->where('company_id', getCompany()->id)->pluck('fullName', 'id'))->searchable()->preload()->label('Logistic'),
                    Forms\Components\Select::make('employee_operation_id')->required()->options(Employee::query()->where('company_id', getCompany()->id)->pluck('fullName', 'id'))->searchable()->preload()->label('Operation'),
                    Forms\Components\Select::make('currency')->required()->options(getCurrency())->searchable()->preload()->label('Currency'),
                    Forms\Components\FileUpload::make('file')->downloadable()->columnSpanFull(),
                    Forms\Components\Textarea::make('description')->columnSpanFull()->nullable()
                ])->columns(5),
                Repeater::make('Requested Items')->required()
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
                                $set('total', number_format(($q * $price) + (($q * $price * $tax)/100) + (($q * $price * $freights)/100)));
                            }
                        })->live(true)->required()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
                        Forms\Components\TextInput::make('taxes')->afterStateUpdated(function ($state, Get $get, Forms\Set $set) {
                            $freights = $get('freights') === null ? 0 : (float)$get('freights');
                            $q = $get('quantity');
                            $tax = $state === null ? 0 : (float)$state;
                            $price = $get('unit_rate') !== null ? str_replace(',', '', $get('unit_rate')) : 0;
                            $set('total', number_format(($q * $price) + (($q * $price * $tax)/100) + (($q * $price * $freights)/100)));
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
                        Forms\Components\TextInput::make('freights')->afterStateUpdated(function ($state, Get $get, Forms\Set $set) {
                            $tax = $get('taxes') === null ? 0 : (float)$get('taxes');
                            $q = $get('quantity');
                            $freights = $state === null ? 0 : (float)$state;
                            $price = $get('unit_rate') !== null ? str_replace(',', '', $get('unit_rate')) : 0;
                            $set('total', number_format(($q * $price) + (($q * $price * $tax)/100) + (($q * $price * $freights)/100)));
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
                            $data[] = ['purchase_request_item_id' => $item->id, 'description'=>$item->description,'quantity' => $item->quantity, 'unit_rate' => 0, 'taxes' => 0, 'freights' => 0];
                        }
                        return $data;
                    })
                    ->columns(6)->addable(false)->columnSpanFull()

            ]);

    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('party.name')->label('Vendor Name'),
                Tables\Columns\TextColumn::make('date')->label('Date')->date(),
                Tables\Columns\TextColumn::make('employee.fullName')->label('Logistic'),
                Tables\Columns\TextColumn::make('employeeOperation.fullName')->label('Operation'),
                Tables\Columns\ImageColumn::make('file')->label('File'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->visible($this->ownerRecord->is_quotation)->modalWidth(MaxWidth::MaxContent)->action(function ($data) {

                    $id = getCompany()->id;
                    $quotation = Quotation::query()->create([
                        'purchase_request_id' => $this->ownerRecord->id,
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
                            'total'=>$item['total']
                        ]);
                    }
                    Notification::make('add quotation')->success()->color('success')->title('Quotation Added')->send()->sendToDatabase(auth()->user());

                }),
            ])
            ->actions([
               Tables\Actions\ViewAction::make()->infolist(function ($record){

                   return [
                       Section::make([
                           TextEntry::make('party.name')->label('Vendor Name'),
                           TextEntry::make('date')->label('Date')->date(),
                           TextEntry::make('employee.fullName')->label('Logistic'),
                           TextEntry::make('employeeOperation.fullName')->label('Operation'),
                           TextEntry::make('total')->label('Total')->badge()->state(function ($record){
                               $total=0;
                               foreach ($record->quotationItems as $quotationItem){
                                   $total+=$quotationItem->item->quantity *$quotationItem->unit_rate;
                               }
                               return number_format($total);
                           }),
                           ImageEntry::make('file'),
                           RepeatableEntry::make('quotationItems')->schema([
                               TextEntry::make('item')->label('Item')->state(fn($record)=>$record->item->product->title . " (" . $record->item->product->sku . ")"),
                               TextEntry::make('unit_rate')->label('Unit Rate')->numeric(),
                               TextEntry::make('item.quantity')->label('Quantity')->numeric(),
                               TextEntry::make('taxes')->label('Taxes')->numeric(),
                               TextEntry::make('freights')->label('Freights')->numeric(),
                               TextEntry::make('total')->numeric(),
                           ])->columns(6)->columnSpanFull()
                       ])->columns()
                   ];
               }),
//                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->visible($this->ownerRecord->bid===null ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
