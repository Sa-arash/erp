<?php

namespace App\Filament\Admin\Widgets;

use App\Filament\Admin\Resources\PurchaseRequestResource\Pages\ViewPurcheseRequest;
use App\Models\Employee;
use App\Models\PurchaseRequest;
use App\Models\Structure;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section as ComponentsSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Validation\Rules\Unique;

class MyPurchaseRequest extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PurchaseRequest::query()->where('employee_id', getEmployee()->id)->orderBy('id','desc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('purchase_number')->label('PR NO')->searchable(),
                    Tables\Columns\TextColumn::make('request_date')->date()->sortable(),
                // Tables\Columns\TextColumn::make('employee.fullName')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('department')
                // ->state(fn($record)=>$record->employee->department->title)
                //     ->numeric()
                //     ->sortable(),
                //     Tables\Columns\TextColumn::make('location')
                //     ->state(fn($record)=>$record->employee->structure->title)
                //         ->numeric()
                //         ->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->tooltip(function ($record){
                    return $record->approvals->last()?->approve_date;
                })->alignCenter(),
                Tables\Columns\TextColumn::make('total')->state(function ($record){
                    $total=0;
                    foreach ($record->items as $item){
                        $total+=$item->quantity *$item->estimated_unit_cost;
                    }
                    return $total;
                })->numeric(),

                // Tables\Columns\TextColumn::make('warehouse_status_date')
                //     ->date()
                //     ->sortable(),

                // Tables\Columns\TextColumn::make('department_manager_status_date')
                //     ->date()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('ceo_status_date')
                //     ->date()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('purchase_date')
                //     ->date()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('created_at')
                //     ->dateTime()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
                // Tables\Columns\TextColumn::make('updated_at')
                //     ->dateTime()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),


            ])


->actions([
    Action::make('view')->modalWidth(MaxWidth::Full)->infolist([
        ComponentsSection::make('request')->schema([
            TextEntry::make('request_date')->date(),
            TextEntry::make('purchase_number')->label('PR NO')->badge(),
            TextEntry::make('employee.fullName'),

            TextEntry::make('description')->columnSpanFull()->label('Description'),
        ])->columns(3),

        RepeatableEntry::make('items')->schema([
            TextEntry::make('product.info')->badge(),
            TextEntry::make('unit.title')->badge(),
            TextEntry::make('quantity'),
            TextEntry::make('estimated_unit_cost')->numeric(),
            TextEntry::make('project.name')->badge(),
            TextEntry::make('description')->columnSpanFull(),
            TextEntry::make('head_decision')->badge()->label('Head Of Department Decision'),
            TextEntry::make('head_comment')->label('Head Of Department Comment')->badge(),
            TextEntry::make('ceo_decision')->badge()->label('CEO Decision'),
            TextEntry::make('ceo_comment')->badge()->label('CEO Comment'),
        ])->columns(5),
        RepeatableEntry::make('approvals')->schema([
            TextEntry::make('employee.fullName'),
            TextEntry::make('created_at')->label('Request Date')->date(),
            TextEntry::make('status')->badge(),
            TextEntry::make('comment')->badge(),
            TextEntry::make('approve_date')->date(),
        ])->columns(5)
    ]),
])
            ->headerActions([
                Action::make('Request Purchase')->label('Request Purchase') ->modalWidth(MaxWidth::FitContent  )->form([
                    Section::make('')->schema([
                        TextInput::make('purchase_number')->default(function (){
                            $puncher= PurchaseRequest::query()->where('company_id',getCompany()->id)->latest()->first();
                            if ($puncher){
                                return  generateNextCodePO($puncher->purchase_number);
                            }else{
                                return "0001";
                            }
                        })->readOnly()->label('PR Number')->unique(modifyRuleUsing: function (Unique $rule) {return $rule->where('company_id', getCompany()->id);})->unique('purchase_requests', 'purchase_number')->required()->numeric(),
                        DatePicker::make('request_date')->default(now())->label('Request Date')->required(),
                        Textarea::make('description')->columnSpanFull()->label('Description'),
                        Repeater::make('Requested Items')
                            ->schema([
                                Select::make('product_id')->searchable()->preload()->label('Product')->options(function (){
                                    $data=[];
                                    foreach (getCompany()->products as $product){
                                        $data[$product->id]=$product->title."(SKU#".$product->sku.")";
                                    }
                                    return $data;
                                })->required(),
                                Select::make('unit_id')->searchable()->preload()->label('Unit')->options(getCompany()->units->pluck('title', 'id'))->required(),
                                TextInput::make('quantity')->required()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
                                TextInput::make('estimated_unit_cost')->label('Estimated Unit Cost')->numeric()->mask(RawJs::make('$money($input)'))->stripCharacters(',')->required(),
                                Select::make('project_id')->searchable()->preload()->label('Project')->options(getCompany()->projects->pluck('name', 'id')),
                                Textarea::make('description')->columnSpan(5)->label('Product Name And Description ')->required(),

                            ])
                            ->columns(5)
                            ->columnSpanFull(),
                    ])->columns(2)
                ])->action(function ($data){
                    $employee=getEmployee();
                    $company=getCompany();
                    $data['company_id']=$company->id;
                    $data['employee_id']=$employee->id;
                    $data['status']='Requested';
                    $request= PurchaseRequest::query()->create($data);
                    foreach ($data['Requested Items'] as $requestedItem) {
                        $requestedItem['company_id']=$company->id;
                        $request->items()->create($requestedItem);
                    }
                    if ($employee->department->employee_id){
                        if ($employee->department->employee_id ===$employee->id){
                            $request->approvals()->create([
                                'employee_id'=>$employee->department->employee_id,
                                'company_id'=>$company->id,
                                'position'=>'Head Department',
                                'status'=>"Approve",
                                'approve_date'=>now()
                            ]);
                            $request->update(['status'=>'FinishedHead']);
                            $CEO=Employee::query()->firstWhere('user_id',$company->user_id);
                            $request->approvals()->create([
                                'employee_id'=>$CEO->id,
                                'company_id'=>$company->id,
                                'position'=>'CEO',
                                'status'=>"Pending"
                            ]);

                        }else{
                            $request->approvals()->create([
                                'employee_id'=>$employee->department->employee_id,
                                'company_id'=>$company->id,
                                'position'=>'Head Department'
                            ]);
                        }
                    }

                })
            ])

        ;
    }

    public static function getPages(): array
    {
        return [

            'view' => ViewPurcheseRequest::route('/{record}/view'),
        ];
    }
}
