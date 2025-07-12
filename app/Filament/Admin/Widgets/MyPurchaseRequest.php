<?php

namespace App\Filament\Admin\Widgets;

use App\Events\PrRequested;
use App\Filament\Admin\Resources\PurchaseRequestResource;
use App\Filament\Admin\Resources\PurchaseRequestResource\Pages\ViewPurcheseRequest;
use App\Models\Product;
use App\Models\PurchaseRequest;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section as ComponentsSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Support\Enums\IconSize;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;

class MyPurchaseRequest extends BaseWidget
{

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table->heading('My Purchase Requests (PR)')
            ->query(
                PurchaseRequest::query()->where('company_id',getCompany()->id)
            )->defaultSort('id', 'desc')
            ->filters([
                getFilterSubordinate()
            ])
            ->columns([
                Tables\Columns\TextColumn::make('No')->rowIndex(),
                Tables\Columns\TextColumn::make('employee.fullName')->label('fullName'),
                Tables\Columns\TextColumn::make('purchase_number')->label('PR No')->searchable(),
                Tables\Columns\TextColumn::make('description')->wrap()->label('Description')->searchable(),
                Tables\Columns\TextColumn::make('request_date')->dateTime()->sortable(),

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
                    })->tooltip(function ($record) {
                        return $record->approvals->last()?->approve_date;
                    })
                    ->sortable()->badge(),
                Tables\Columns\TextColumn::make('need_change')->state(fn($record) => $record->need_change ? "Yes" : "No")->color(fn($record) => $record->need_change ? "warning" : "success")->label('Need Revise')->badge()->alignCenter(),
                Tables\Columns\TextColumn::make('total')->state(function ($record) {
                    $total = 0;
                    foreach ($record->items as $item) {
                        $total += $item->quantity * $item->estimated_unit_cost;
                    }
                    return number_format($total,2).' '.$record->currency?->symbol;
                })->numeric(),
                Tables\Columns\ImageColumn::make('approvals')->state(function ($record) {
                    $data = [];
                    foreach ($record->approvals as $approval) {
                        if ($approval->status->value == "Approve") {
                            if ($approval->employee->media->where('collection_name', 'images')->first()?->original_url) {
                                $data[] = $approval->employee->media->where('collection_name', 'images')->first()?->original_url;
                            } else {
                                $data[] = $approval->employee->gender === "male" ? asset('img/user.png') : asset('img/female.png');
                            }
                        }
                    }
                    return $data;
                })->circular()->stacked(),
                Tables\Columns\TextColumn::make('bid.total_cost')->alignCenter()->label('Total Final Price' )->numeric(),


            ])


->actions([
    Tables\Actions\DeleteAction::make()->hidden(function ($record) {
        if ($record->employee_id != getEmployee()->id){
            return true;
        }
//        dd($record->approvals);
        return $record->approvals()->where('status', 'Approve')->count();
    })->action(function ($record) {
        $record->approvals()->delete();
        $record->delete();
    }),
    Tables\Actions\EditAction::make()->fillForm(function ($record) {
        $data = [];
        foreach ($record->items as $item) {
            $department = $item->department;
            $type = $item->product->product_type === "service" ? "0" : "1";

            $image= $item->getFirstMedia('document');

            $item = $item->toArray();
            if ($image){
                $path = Str::after($image->original_url, 'images/');

                $item['images']=[
                    'name'=>$path
                ];

            }

            $item['department_id'] = $department;
            $item['type'] = $type;
            $data[] = $item;
        }

        return [
            'purchase_number' => $record->purchase_number,
            'request_date' => $record->request_date,
            'currency_id' => $record->currency_id,
            'description' => $record->description,
            'Requested Items' => $data
        ];
    })
        ->form([
        Section::make('')->schema([
            TextInput::make('purchase_number')->readOnly()->label('PR Number')->prefix('ATGT/UNC/')->required()->numeric(),
            DateTimePicker::make('request_date')->readOnly()->default(now())->label('Request Date')->required(),
            Select::make('currency_id')->live()->label('Currency')->default(defaultCurrency()?->id)->required()->relationship('currency', 'name', modifyQueryUsing: fn($query) => $query->where('company_id', getCompany()->id))->searchable()->preload(),
            Textarea::make('description')->required()->columnSpanFull()->label('Description'),
            Repeater::make('Requested Items')
                ->addActionLabel('Add Item')
                ->schema([
                    Select::make('type')->required()->options(['Service', 'Product'])->default(1)->searchable(),
                    Select::make('department_id')->columnSpan(['default' => 8, 'md' => 2, 'xl' => 2, '2xl' => 1])->label('Section')->live()->options(getCompany()->departments->pluck('title', 'id'))->searchable()->preload()->default(getEmployee()->department_id),
                    Select::make('product_id')->columnSpan(['default' => 8, 'md' => 2])->label('Product/Service')->options(function (Get $get) {
                        if ($get('department_id')) {
                            $data = [];
                            $products = getCompany()->products()->where('product_type', $get('type') === "0" ? '=' : '!=', 'service')->where('department_id', $get('department_id'))->pluck('title', 'id');
                            $i = 1;
                            foreach ($products as $key => $product) {
                                $data[$key] = $i . ". " . $product;
                                $i++;
                            }
                            return $data;
                        }
                    })->required()->searchable()->preload()->afterStateUpdated(function (Set $set, $state) {
                        $product = Product::query()->firstWhere('id', $state);
                        if ($product) {
                            $set('unit_id', $product->unit_id);
                        }
                    })->live(true)->getSearchResultsUsing(fn(string $search, Get $get): array => Product::query()->where('company_id', getCompany()->id)->where('title', 'like', "%{$search}%")->orWhere('second_title', 'like', "%{$search}%")->where('department_id', $get('department_id'))->pluck('title', 'id')->toArray())->getOptionLabelsUsing(function (array $values) {
                        $data = [];
                        $products = getCompany()->products->whereIn('id', $values)->pluck('title', 'id');
                        $i = 1;
                        foreach ($products as $key => $product) {
                            $data[$key] = $i . ". " . $product;
                            $i++;
                        }
                        return $data;

                    }),
                    Select::make('unit_id')->columnSpan(['default' => 8, 'md' => 2, '2xl' => 1])->searchable()->preload()->label('Unit')->options(getCompany()->units->pluck('title', 'id'))->required(),
                    TextInput::make('quantity')->columnSpan(['default' => 8, 'md' => 1, '2xl' => 1])->required()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
                    TextInput::make('estimated_unit_cost')->columnSpan(['default' => 8, 'md' => 1, '2xl' => 1])->label('Estimated Unit Cost')->numeric()->mask(RawJs::make('$money($input)'))->stripCharacters(',')->required(),
                    Select::make('project_id')->columnSpan(['default' => 8, 'md' => 2, '2xl' => 1])->searchable()->preload()->label('Project')->options(getCompany()->projects->pluck('name', 'id')),
                    Textarea::make('description')->columnSpan(7)->label('Product Description ')->required(),
                    Placeholder::make('status')->columnSpan(1)->label('Item Status')->content(function ($state){
                        $status= match ($state){
                            'approve' => 'Approved',
                            'reject' => 'Rejected',
                            'Revise' => 'Revise',
                            default => 'Pending',
                        };
                         $color=match ($status) {
                            'Approved' => 'green',
                            'Rejected' => 'red',
                            'Revise' => 'orange',
                            default => '#196fe0',
                        };
                        return new HtmlString("<span style='color: {$color}'>{$status}</span>");
                    }),
                    FileUpload::make('images')->downloadable()->label('Document')->columnSpanFull()->nullable()
                ])
                ->columns(8)
                ->columnSpanFull(),
        ])->columns(3)
    ])->action(function ($data, $record) {
            $data['need_change'] = 0;
            $record->update($data);
        $ids = [];
        $company = getCompany()->id;
        foreach ($data['Requested Items'] as $datum) {
            if (count($datum) > 9) {
                unset($datum['product']);;
                unset($datum['department_id']);
                unset($datum['type']);
                unset($datum['created_at']);
                unset($datum['updated_at']);
                $item = $record->items()->where('id', $datum['id'])->first();
                if ($item) {
                    $mediaItem = $datum['images'] ?? null;
                    if (isset($mediaItem)){
                       if ($item->getFirstMedia('document')?->name){
                           if (!strstr($mediaItem,$item->getFirstMedia('document')?->name)){
                               $item->clearMediaCollection('document');
                               $item->addMedia(public_path('images/'.$mediaItem))->toMediaCollection('document');
                           }
                       }
                    }
                    unset($datum['images']);
                    $item->update($datum);
                }
            } else {
                $datum['company_id'] = $company;
                $item = $record->items()->create($datum);
                $mediaItem = $datum['images'] ?? null;
                if (isset($mediaItem)){
                  $item->addMedia(public_path('images/'.$mediaItem))->toMediaCollection('document');
                }
            }
            $ids[] = $item->id;
        }
        $record->items()->whereNotIn('id', $ids)->delete();
        Notification::make('success')->success()->title('Edited Successfully')->send();
    })->modalWidth(MaxWidth::Full)->visible(fn($record)=>$record->status->value==="Requested" or $record->need_change)->hidden(fn($record)=>$record->employee_id != getEmployee()->id),
    Action::make('view')->slideOver()->modalWidth(MaxWidth::Full)->infolist([
        ComponentsSection::make('Purchase Request')->schema([
            TextEntry::make('request_date')->dateTime(),
            TextEntry::make('purchase_number')->label('PR No')->badge(),
            TextEntry::make('employee.fullName'),
            TextEntry::make('description')->columnSpanFull()->label('Description'),
        ])->columns(3),

        TextEntry::make('content')
            ->state(function ($record) {
                $items = $record->items ?? [];
                $approves=$record->approvals->where('approve_date','!=',null)->all();
                $headers = [
                    'Product/Service', 'Unit', 'Quantity', 'Est. Unit Cost', 'Project',
                    'Description','Warehouse Commenter ', 'Warehouse Decision', 'Warehouse Comment' ,'Verified by ' ,
                    'Verification Decision', 'Verification Comment', 'Approved by ' ,
                    'Approval Decision', 'Approval Comment'
                ];

                $html = '<table style="width: 100%; border-collapse: collapse; font-size: 12px;margin-left: 5%">';

                // Header Row
                $html .= '<tr>';
                foreach ($headers as $header) {
                    $html .= '<th style="background-color: white; color: black; border: 1px solid #ccc; padding: 6px;">' . $header . '</th>';
                }
                $html .= '</tr>';
                function getColor($status)
                {
                    return match ($status) {
                        'Approved' => 'green',
                        'Rejected' => 'red',
                        'Revise' => 'orange',
                        default => '#196fe0',
                    };
                }
                // Data Rows
                foreach ($items as $item) {
                    $html .= '<tr>';
                    $html .= '<td style="border: 1px solid #ccc; padding: 6px;">' . ($item['product']['info'] ?? '') . '</td>';
                    $html .= '<td style="border: 1px solid #ccc; padding: 6px;">' . ($item['unit']['title'] ?? '') . '</td>';
                    $html .= '<td style="border: 1px solid #ccc; padding: 6px;">' . ($item['quantity'] ?? '') . '</td>';
                    $html .= '<td style="border: 1px solid #ccc; padding: 6px;">' . ($item['estimated_unit_cost'] ?? '') . '</td>';
                    $html .= '<td style="border: 1px solid #ccc; padding: 6px;">' . ($item['project']['name'] ?? '') . '</td>';
                    $html .= '<td style="border: 1px solid #ccc; padding: 6px;">' . ($item['description'] ?? '') . '</td>';

                    // Warehouse Decision
                    $warehouseDecision = match ($item['clarification_decision'] ?? null) {
                        'approve' => 'Approved',
                        'reject' => 'Rejected',
                        'Revise' => 'Revise',
                        default => 'Pending',
                    };
                    $color=getColor($warehouseDecision);
                    $html .= '<td style="border: 1px solid #ccc; padding: 6px;">' . ($approves[0]?->employee?->fullName ?? '') . '</td>';
                    $html .= "<td style='border: 1px solid #ccc; padding: 6px;color:{$color}'>" . $warehouseDecision . "</td>";
                    $html .= '<td style="border: 1px solid #ccc; padding: 6px;">' . ($item['clarification_comment'] ?? '') . '</td>';

                    // Verification Decision
                    $verificationDecision = match ($item['verification_decision'] ?? null) {
                        'approve' => 'Approved',
                        'reject' => 'Rejected',
                        'Revise' => 'Revise',
                        default => 'Pending',
                    };
                    $color=getColor($verificationDecision);
                    $html .= '<td style="border: 1px solid #ccc; padding: 6px;">' . ($approves[1]?->employee?->fullName ?? '') . '</td>';
                    $html .= "<td style='border: 1px solid #ccc; padding: 6px;color:{$color}'>" . $verificationDecision . "</td>";
                    $html .= '<td style="border: 1px solid #ccc; padding: 6px;">' . ($item['verification_comment'] ?? '') . '</td>';

                    // Approval Decision
                    $approvalDecision = match ($item['approval_decision'] ?? null) {
                        'approve' => 'Approved',
                        'reject' => 'Rejected',
                        'Revise' => 'Revise',
                        default => 'Pending',
                    };
                    $color=getColor($approvalDecision);

                    $html .= '<td style="border: 1px solid #ccc; padding: 6px;">' . ($approves[2]?->employee?->fullName ?? '') . '</td>';
                    $html .= "<td style='border: 1px solid #ccc; padding: 6px;color:{$color}'>" . $approvalDecision . "</td>";
                    $html .= '<td style="border: 1px solid #ccc; padding: 6px;">' . ($item['approval_comment'] ?? '') . '</td>';

                    $html .= '</tr>';
                }

                $html .= '</table>';

                return $html;
            })
            ->label('Items Overview')
            ->html(),

        RepeatableEntry::make('approvals')->schema([
            ImageEntry::make('employee.image')->circular()->label('')->state(fn($record) => $record->employee->media->where('collection_name', 'images')->first()?->original_url),
            TextEntry::make('employee.fullName')->label(fn($record) => $record->employee?->position?->title),
            TextEntry::make('read_at')->label('Checked at Date')->dateTime(),
            TextEntry::make('status')->state(fn($record)=>match ($record->status->value){
                'Approve'=>"Approved",
                'NotApprove'=>"Not Approved",
                'Pending'=>"Pending",
            })->badge()->color(fn($state)=>match ($state){
                'Approved'=>"success",
                'Not Approved'=>"danger",
                'Pending'=>"primary",
            }),            TextEntry::make('comment')->tooltip(fn($record) => $record->comment)->limit(50),
            TextEntry::make('approve_date')->dateTime(),
            ImageEntry::make('employee.signature')->label('')->state(fn($record) => $record->status->value === "Approve" ? $record->employee->media->where('collection_name', 'signature')->first()?->original_url : ''),
        ])->columns(7)->columnSpanFull()

    ]),
    Tables\Actions\Action::make('prPDF')->label('Print ')->iconSize(IconSize::Large)->icon('heroicon-s-printer')->url(fn($record) => route('pdf.purchase', ['id' => $record->id]))->openUrlInNewTab(),
    Tables\Actions\Action::make('Duplicate')->iconSize(IconSize::Large)->icon('heroicon-o-clipboard-document-check')->label('Duplicate')->url(fn($record)=>PurchaseRequestResource::getUrl('replicate',['my','id'=>$record->id]))

])
            ->headerActions([
                Action::make('Purchase Request ')->slideOver()->label('New PR ') ->modalWidth(MaxWidth::Full  )->form([
                    Section::make('')->schema([
                        TextInput::make('purchase_number')->default(function (){
                            $puncher= PurchaseRequest::query()->where('company_id',getCompany()->id)->latest()->first();
                            if ($puncher){
                                return  generateNextCodePO($puncher->purchase_number);
                            }else{
                                return "00001";
                            }
                        })->readOnly()->label('PR Number')->prefix('ATGT/UNC/')->unique(modifyRuleUsing: function (Unique $rule) {return $rule->where('company_id', getCompany()->id);})->unique('purchase_requests', 'purchase_number')->required()->numeric()->hintAction(\Filament\Forms\Components\Actions\Action::make('update')->label('Update NO')->action(function (Set $set){
                            $puncher= PurchaseRequest::query()->where('company_id',getCompany()->id)->latest()->first();
                            if ($puncher){
                                  $set('purchase_number',generateNextCodePO($puncher->purchase_number));
                            }else{
                                $set('purchase_number','00001');
                            }
                        })),
                        DateTimePicker::make('request_date')->readOnly()->default(now())->label('Request Date')->required(),
                        Select::make('currency_id')->live()->label('Currency')->default(defaultCurrency()?->id)->required()->relationship('currency', 'name', modifyQueryUsing: fn($query) => $query->where('company_id', getCompany()->id))->searchable()->preload(),
                        Textarea::make('description')->required()->columnSpanFull()->label('Description'),
                        Repeater::make('Requested Items')
                        ->addActionLabel('Add Item')
                            ->schema([
                                Select::make('type')->required()->options(['Service', 'Product'])->default(1)->searchable(),
                                Select::make('department_id')->columnSpan(['default' => 8, 'md' => 2, 'xl' => 2, '2xl' => 1])->label('Section')->live()->options(getCompany()->departments->pluck('title', 'id'))->searchable()->preload()->default(getEmployee()->department_id),
                                Select::make('product_id')->columnSpan(['default' => 8, 'md' => 2])->label('Product/Service')->options(function (Get $get) {
                                    if ($get('department_id')) {
                                        $data = [];
                                        $products = getCompany()->products()->where('product_type', $get('type') === "0" ? '=' : '!=', 'service')->where('department_id', $get('department_id'))->pluck('title', 'id');
                                        $i = 1;
                                        foreach ($products as $key => $product) {
                                            $data[$key] = $i . ". " . $product;
                                            $i++;
                                        }
                                            return $data ;
                                        }
                                    })->required()->searchable()->preload()->afterStateUpdated(function (Set $set,$state){
                                        $product=Product::query()->firstWhere('id',$state);
                                        if ($product){
                                            $set('unit_id',$product->unit_id);
                                        }
                                    })->live(true)->getSearchResultsUsing(fn (string $search,Get $get): array => Product::query()->where('company_id',getCompany()->id)->where('title','like',"%{$search}%")->orWhere('second_title','like',"%{$search}%")->where('department_id',$get('department_id'))->pluck('title', 'id')->toArray())->getOptionLabelsUsing(function(array $values){
                                            $data=[];
                                            $products=getCompany()->products->whereIn('id', $values)->pluck('title', 'id');
                                            $i=1;
                                            foreach ($products as $key=> $product){
                                                $data[$key]=$i.". ". $product;
                                                $i++;
                                            }
                                    return $data;

                                }),
                                Textarea::make('description')->columnSpan(3)->label('Product Description ')->required(),
                                Select::make('unit_id')->columnSpan(['default' => 8, 'md' => 2, '2xl' => 1])->searchable()->preload()->label('Unit')->options(getCompany()->units->pluck('title', 'id'))->required(),
                                TextInput::make('quantity')->columnSpan(['default' => 8, 'md' => 2, '2xl' => 1])->required()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
                                TextInput::make('estimated_unit_cost')->live()->columnSpan(['default' => 8, 'md' => 2, '2xl' => 1])->label('Estimated Unit Cost')->numeric()->mask(RawJs::make('$money($input)'))->stripCharacters(',')->required(),
                                Select::make('project_id')->columnSpan(['default' => 8, 'md' => 2, '2xl' => 1])->searchable()->preload()->label('Project')->options(getCompany()->projects->pluck('name', 'id')),
                                Placeholder::make('total')->columnSpan(['default'=>8,'md'=>1,'xl'=>1])
                                    ->content(fn($state, Get $get) => number_format(((int)str_replace(',', '', $get('quantity'))) * ((float)str_replace(',', '', $get('estimated_unit_cost'))),2)),
                                FileUpload::make('images')->label('Document')->columnSpanFull()->nullable()
                            ])
                            ->columns(12)
                            ->columnSpanFull(),
                    ])->columns(3)
                ])->action(function ($data){

                    $employee=getEmployee();
                    $company=getCompany();
                    $data['company_id']=$company->id;
                    $data['employee_id']=$employee->id;
                    $data['request_date']=now();
                    $data['status']='Requested';
                    $request= PurchaseRequest::query()->create($data);
                    foreach ($data['Requested Items'] as $requestedItem) {
                        $requestedItem['company_id']=$company->id;
                        $item= $request->items()->create($requestedItem);
                        $mediaItem = $requestedItem['images'] ?? null;
                        if (isset($mediaItem)){
                            $item->addMedia(public_path('images/'.$mediaItem))->toMediaCollection('document');
                        }
                    }
//                    broadcast(new PrRequested($request));

                    sendApprove($request,'PR Warehouse_approval');
                    Notification::make('success')->success()->title('Successfully Submitted')->send();
                })
            ]);
    }

    public static function getPages(): array
    {
        return [

            'view' => ViewPurcheseRequest::route('/{record}/view'),
        ];
    }
}
