<?php

namespace App\Filament\Admin\Resources\AssetResource\Pages;

use App\Filament\Admin\Resources\AssetResource;
use App\Models\AssetEmployee;
use App\Models\Person;
use App\Models\Structure;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\MaxWidth;

class ViewAsset extends ViewRecord
{
    protected static string $resource = AssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Check OUT')->label('Check OUT')->color('success')->form([
                \Filament\Forms\Components\Section::make([
                    Select::make('employee_id')->columnSpan(2)->label('Employee')->options(function () {
                        $data = [];
                        $employees = getCompany()->employees;
                        foreach ($employees as $employee) {
                            $data[$employee->id] = $employee->fullName . " (ID # " . $employee->ID_number . " )";
                        }
                        return $data;
                    })->searchable()->requiredWithout('person')->prohibits('person'),
                    Select::make('person')->label('Personnel')->options(function (){
                        $data=[];
                        $persons=getCompany()->people;
                        foreach ($persons as $person){
                            $data[$person->id]=$person->name.' ('.$person->number.')';
                        }
                        return $data;
                    })->createOptionForm([
                           \Filament\Forms\Components\Section::make([
                               TextInput::make('name')->required()->maxLength(255),
                               TextInput::make('number')->default(function () {
                                   $lastPerson = Person::query()->where('company_id', getCompany()->id)->latest()->first();
                                   if ($lastPerson) {
                                       return getNextCodePerson($lastPerson->number, 'PSN');
                                   } else {
                                       return 'PSN00001';
                                   }
                               })->readOnly()->required()->maxLength(255),
                               Select::make('person_group')->options(getCompany()->person_group)
                                   ->createOptionForm([
                                       TextInput::make('title')->required()
                                   ])->createOptionUsing(function ($data) {
                                       $array = getCompany()->person_group;
                                       if (isset($array)) {
                                           $array[$data['title']] = $data['title'];
                                       } else {
                                           $array = [$data['title'] => $data['title']];
                                       }
                                       getCompany()->update(['person_group' => $array]);
                                       return $data['title'];
                                   })->searchable(),
                               TextInput::make('job_title')->maxLength(255)->default(null),
                               \Filament\Forms\Components\Section::make([
                                   TextInput::make('work_phone')->maxLength(255)->default(null),
                                   TextInput::make('home_phone')->maxLength(255)->default(null),
                                   TextInput::make('mobile_phone')->tel()->maxLength(255)->default(null),
                               ])->columns(3),
                               TextInput::make('pager')->maxLength(255)->default(null),
                               TextInput::make('email')->email()->maxLength(255)->default(null),
                               Textarea::make('note')->columnSpanFull(),
                           ])->columns(2)
                    ])->createOptionUsing(function ($data) {
                        $data['company_id']=getCompany()->id;
                        return Person::query()->create($data);
                    })->searchable()->preload()->requiredWithout('employee_id')->prohibits('employee_id'),
                    Textarea::make('description')->label('Comment')->columnSpanFull(),
                    Select::make('warehouse_id')->live()->label('Warehouse/Building')->options(function () {
                        $data = [];
                        foreach (getCompany()->warehouses as $warehouse) {
                            $type=$warehouse->type ? "Warehouse" : "Building";
                            $data[$warehouse->id] = $warehouse->title . " (" . $type . ")";
                        }
                        return $data;
                    })->searchable()->preload(),
                    SelectTree::make('structure_id')->label('Location')->defaultOpenLevel(2)->model(Structure::class)->relationship('parent', 'title', 'parent_id', modifyQueryUsing: function ($query, Get $get) {
                        return $query->where('warehouse_id', $get('warehouse_id'));
                    }),
                    DatePicker::make('due_date'),
                ])->columns(3)
            ])->action(function ($record,$data){

                $company=getCompany();
                if ($data['employee_id']){
                    $record->update(['status'=>'inuse','warehouse_id'=>$data['warehouse_id'],'structure_id'=>$data['structure_id'],'check_out_to'=>$data['employee_id']]);
                    $assetEmployee=AssetEmployee::query()->firstWhere('employee_id',$data['employee_id']);
                }else{
                    $record->update(['status'=>'inuse','warehouse_id'=>$data['warehouse_id'],'structure_id'=>$data['structure_id'],'check_out_person'=>$data['person']]);
                    $assetEmployee=AssetEmployee::query()->firstWhere('person_id',$data['person']);
                }
                if ($assetEmployee){
                    $assetEmployee->assetEmployeeItem()->create([
                        'company_id'=>$company->id,
                        'asset_id'=>$record->id,
                        'due_date'=>$data['due_date'],
                        'warehouse_id'=>$data['warehouse_id'],
                        'type'=>'Assigned',
                        'structure_id'=>$data['structure_id'],
                        'description'=>$data['description']
                    ]);
                    $record->update(['check_out_to'=>$assetEmployee->employee_id,'status'=>'inuse']);
                }else{
                    $assetEmployee=AssetEmployee::query()->create([
                        'company_id'=>$company->id,
                        'employee_id'=>$data['employee_id'],
                        'date'=>now(),
                        'person_id'=>$data['person']
                    ]);
                    $assetEmployee->assetEmployeeItem()->create([
                        'company_id'=>$company->id,
                        'asset_id'=>$record->id,
                        'due_date'=>$data['due_date'],
                        'warehouse_id'=>$data['warehouse_id'],
                        'type'=>'Assigned',
                        'structure_id'=>$data['structure_id'],
                        'description'=>$data['description']
                    ]);
                }
                sendSuccessNotification();
            })->disabled(fn($record) => $record->check_out_to or $record->check_out_person  )->modalWidth(MaxWidth::FiveExtraLarge),

            Action::make('Check IN')->label('Check IN')->color('warning')->fillForm(function ($record){
                return [
                    'employee_id'=>$record->check_out_to,
                    'person'=>$record->check_out_person
                ];
            })->form([
               \Filament\Forms\Components\Section::make([
                   Select::make('employee_id')->label('Employee')->options(function () {
                       $data = [];
                       $employees = getCompany()->employees;
                       foreach ($employees as $employee) {
                           $data[$employee->id] = $employee->fullName . " (ID # " . $employee->ID_number . " )";
                       }
                       return $data;
                   })->disabled()->searchable()->requiredWithout('person')->prohibits('person'),
                   Select::make('person')->disabled()->label('Personnel')->options(function (){
                       $data=[];
                       $persons=getCompany()->people;
                       foreach ($persons as $person){
                           $data[$person->id]=$person->name.' ('.$person->number.')';
                       }
                       return $data;
                   })->searchable()->preload()->requiredWithout('employee_id')->prohibits('employee_id'),
                   Textarea::make('description')->label('Comment')->columnSpanFull(),
                   Select::make('warehouse_id')->live()->label('Warehouse/Building')->options(function () {
                       $data = [];
                       foreach (getCompany()->warehouses as $warehouse) {
                           $type=$warehouse->type ? "Warehouse" : "Building";
                           $data[$warehouse->id] = $warehouse->title . " (" . $type . ")";
                       }
                       return $data;
                   })->searchable()->preload(),
                   SelectTree::make('structure_id')->label('Location')->defaultOpenLevel(2)->model(Structure::class)->relationship('parent', 'title', 'parent_id', modifyQueryUsing: function ($query, Get $get) {
                       return $query->where('warehouse_id', $get('warehouse_id'));
                   }),
               ])->columns()
            ])->action(function ($data,$record){

                $company=getCompany();
                $employee=$record->employees->last()?->assetEmployee?->employee_id;
                $person=$record->employees->last()?->assetEmployee?->person_id;
                if ($employee){
                    $assetEmployee=AssetEmployee::query()->firstWhere('employee_id',$employee);
                }else{
                    $assetEmployee=AssetEmployee::query()->firstWhere('person_id',$person);
                }
                if ($assetEmployee) {
                    $assetEmployee->assetEmployeeItem()->create([
                        'company_id' => $company->id,
                        'asset_id' => $record->id,
                        'due_date' => null,
                        'warehouse_id' => $data['warehouse_id'],
                        'type' => 'Returned',
                        'structure_id' => $data['structure_id'],
                        'description' => $data['description']
                    ]);
                    $record->update(['status'=>'inStorageUsable','warehouse_id'=>$data['warehouse_id'],'structure_id'=>$data['structure_id'],'check_out_to' => null,'check_out_person'=>null]);
                }
                sendSuccessNotification();

            })->disabled(fn($record)=>$record->check_out_person ===null and $record->check_out_to===null),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make([


                Group::make([
                    TextEntry::make('product.sku')->label('SKU')->badge()->inlineLabel(),
                    TextEntry::make('product.title')->inlineLabel(),
                    TextEntry::make('description')->inlineLabel(),
                    TextEntry::make('serial_number')->label("Serial Number")->badge()->inlineLabel(),
                    TextEntry::make('po_number')->label("PO Number")->badge()->inlineLabel(),
                    TextEntry::make('status')->state(fn($record) => match ($record->status) {
                        'inuse' => "In Use",
                        'inStorageUsable' => "In Storage",
                        'loanedOut' => "Loaned Out",
                        'outForRepair' => 'Out For Repair',
                        'StorageUnUsable' => " Scrap"
                    })->badge()->inlineLabel(),
                    TextEntry::make('price')->numeric()->inlineLabel(),
                    TextEntry::make('scrap_value')->label("Scrap Value")->numeric()->inlineLabel(),
                    TextEntry::make('warehouse.title')->badge()->inlineLabel(),
                    TextEntry::make('structure.title')->badge()->label('Location')->inlineLabel(),
                    TextEntry::make('check_out_to')->state(function ($record){return $record->check_out_to ? $record->checkOutTo->fullName:$record->person?->name;})->badge()->label('Check Out To')->inlineLabel(),
                    TextEntry::make('party.name')->badge()->label('Vendor')->inlineLabel(),
                    TextEntry::make('buy_date')->inlineLabel()->label('Buy Date'),
                    TextEntry::make('guarantee_date')->inlineLabel()->label('Due Date'),
                    TextEntry::make('warranty_date')->inlineLabel()->label('Warranty End'),
                    TextEntry::make('type')->badge()->label('Asset Type')->inlineLabel(),
                    TextEntry::make('depreciation_years')->inlineLabel()->label('Depreciation Years'),
                    TextEntry::make('depreciation_amount')->inlineLabel()->label('Depreciation Amount'),
                ]),

                Group::make([
                    ImageEntry::make('media.original_url')->state(function ($record) {
                        return $record->media->where('collection_name', 'images')->first()?->original_url;
                    })->disk('public')
                        ->defaultImageUrl(fn($record) => asset('img/defaultAsset.png'))
                        ->alignLeft()->label('Asset Picture')->width(200)->height(200)->extraAttributes(['style' => 'border-radius:50px!important']),


                        TextEntry::make('note'),
                    RepeatableEntry::make('attributes')
                        ->schema([
                            TextEntry::make('title'),
                            TextEntry::make('value'),
                        ])
                        ->columns(3),
                ]),



            ])->columns(2)
        ]);
    }
}
