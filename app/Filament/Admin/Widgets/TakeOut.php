<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Asset;
use App\Models\AssetEmployeeItem;
use App\Models\Unit;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Get;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Support\Enums\IconSize;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use TomatoPHP\FilamentMediaManager\Form\MediaManagerInput;

class TakeOut extends BaseWidget
{

    protected int|string|array $columnSpan = 'full';

protected static ?string $heading='Gate Pass';
    public function table(Table $table): Table
    {
        return $table->emptyStateHeading('No Gate Pass')->heading('Gate Pass')->headerActions([
            Action::make('Take Out')->slideOver()->label('New Gate Pass')->form([
                \Filament\Forms\Components\Section::make([
                    TextInput::make('from')->label('From (Location)')->default(getEmployee()->structure?->title)->required()->maxLength(255),
                    TextInput::make('to')->label('To (Location)')->required()->maxLength(255),
                    DatePicker::make('date')->default(now())->required()->label('Check OUT Date'),
                    DatePicker::make('return_date')->label('Check IN Date'),
                    Textarea::make('reason')->columnSpanFull()->required(),
                    ToggleButtons::make('status')->default('Returnable')->colors(['Returnable' => 'success', 'Non-Returnable' => 'danger'])->live()->required()->grouped()->options(['Returnable' => 'Returnable', 'Non-Returnable' => 'Non-Returnable']),
                    ToggleButtons::make('type')->default('Modification')->required()->grouped()->options(function (Get $get) {
                        if ($get('status') === "Returnable") {
                            return ['Modification' => 'Modification'];
                        } else {
                            return ['Personal Belonging' => 'Personal Belonging', 'Domestic Waste' => 'Domestic Waste', 'Construction Waste' => 'Construction Waste'];
                        }
                    }),
                    FileUpload::make('image')->label('Attached for all sections')->image()->imageEditor()->columnSpan(1),
                    FileUpload::make('supporting')->label('Attached Supporting Document')->image()->imageEditor()->columnSpan(1),
                    Repeater::make('items')->required(function (Get $get){
                        if (!$get('itemsOut')){
                            return true;
                        }
                    })->label('Registered Asset')->addActionLabel('Add to Register Asset')->orderable(false)->schema([
                        Select::make('asset_id')
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                            ->live()->label('Asset')->options(function () {
                                $data = [];
                                $sub = AssetEmployeeItem::selectRaw('MAX(id) as id')
                                    ->whereHas('assetEmployee', function ($q) {
                                        $q->where('employee_id', getEmployee()->id);
                                    })
                                    ->groupBy('asset_id');

                                $assetA=AssetEmployeeItem::query()
                                    ->whereIn('id', $sub)
                                    ->where('type', 'Assigned')->pluck('asset_id')->toArray();

                                $assets = Asset::query()->with('product')->whereIn('id',$assetA)->whereHas('employees', function ($query) {
                                    return $query->whereHas('assetEmployee', function ($query) {
                                        return $query->where('employee_id', getEmployee()->id);
                                    });
                                })->where('company_id', getCompany()->id)->get();
                                foreach ($assets as $asset) {
                                    $data[$asset->id] = $asset->product?->title. " ( SKU #" . $asset->product?->sku . " )";
                                }
                                return $data;
                            })->required()->searchable()->preload(),
                        TextInput::make('remarks')->nullable()
                    ])->columnSpanFull()->columns(),
                    Repeater::make('itemsOut')->required(function (Get $get){
                        if (!$get('items')){
                            return true;
                        }
                    })->label('Unregistered Asset')->addActionLabel('Add to Unregister Asset')->orderable(false)->schema([
                        TextInput::make('name')->required(),
                        TextInput::make('quantity')->required(),
                        Select::make('unit')->searchable()->options(Unit::query()->where('company_id', getCompany()->id)->pluck('title','title'))->required(),
                        TextInput::make('remarks')->nullable(),
                        FileUpload::make('image')->columnSpanFull()->label('Image Upload')->image()->imageEditor(),
                    ])->columnSpanFull()->columns(4)
                ])->columns(4)

            ])->modalWidth(MaxWidth::Full)->action(function ($data) {
                $id = getCompany()->id;
                $data['company_id'] = $id;
                $employee = getEmployee();
                $data['employee_id'] = $employee->id;
                $items = $data['items'];
                unset($data['items']);
                foreach ($data['itemsOut'] as $key=> $datum){
                    $value=$datum;
                    $value['status']='Pending';
                    $data['itemsOut'][$key]=$value;
                }
                $takeOut = \App\Models\TakeOut::query()->create($data);
                foreach ($items as $item) {
                    $item['company_id'] = $id;
                    $takeOut->items()->create($item);
                }
                $media = $data['image'] ?? null;
                if (isset($media)) {
                    $takeOut->addMedia(public_path('images/'.$media))->toMediaCollection('image');
                }
                $media = $data['supporting'] ?? null;
                if (isset($media)) {
                    $takeOut->addMedia(public_path('images/'.$media))->toMediaCollection('supporting');
                }
                $employee = User::whereHas('roles.permissions', function ($query) {
                    $query->where('name', 'security_take::out');
                })->get() ->pluck('employee.id')->toArray();
                $securityIDs =$employee;
                if($securityIDs){
                    foreach ($securityIDs as $security){
                        $takeOut->approvals()->create([
                            'employee_id' => $security,
                            'company_id' => getCompany()->id,
                            'position' => 'Security',
                        ]);
                    }
                }
                Notification::make('success')->color('success')->success()->title('Request Sent')->send()->sendToDatabase(auth()->user());
            })->color('warning')
        ])
            ->query(
                \App\Models\TakeOut::query()->where('company_id',getCompany()->id)
            )->defaultSort('id','desc')
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('employee.fullName'),
                Tables\Columns\TextColumn::make('assets.product.title')->state(fn($record)=> $record->assets->pluck('title')->toArray())->badge()->label('Assets'),
                Tables\Columns\TextColumn::make('itemsOut')->state(function($record){
                    $data=[];
                    if ($record->itemsOut){
                        foreach ($record->itemsOut as $item){
                            $data[]=$item['name'];
                        }
                    }
                    return $data;
                })->limitList(5)->badge(),
                Tables\Columns\TextColumn::make('from'),
                Tables\Columns\TextColumn::make('to'),
                Tables\Columns\TextColumn::make('date')->date(),
                Tables\Columns\TextColumn::make('mood')->color(function ($record){
                    if ($record->mood==="Approved" ){
                        return 'success';
                    }elseif ($record->mood==="NotApproved"){
                        return 'danger';
                    }else{
                        return 'primary';
                    }
                })->label('Request Status')->badge(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('approvals.approve_date')->label('Approval Date'),
                Tables\Columns\TextColumn::make('approvals.comment')->label('Comments')

            ])->actions([
                Tables\Actions\EditAction::make()->visible(fn($record)=>$record->mood ==="Pending")->form([
                    \Filament\Forms\Components\Section::make([
                        TextInput::make('from')->label('From (Location)')->default(getEmployee()->structure?->title)->required()->maxLength(255),
                        TextInput::make('to')->label('To (Location)')->required()->maxLength(255),
                        DatePicker::make('date')->default(now())->required()->label('Check OUT Date'),
                        DatePicker::make('return_date')->label('Check IN Date'),
                        Textarea::make('reason')->columnSpanFull()->required(),
                        ToggleButtons::make('status')->default('Returnable')->colors(['Returnable' => 'success', 'Non-Returnable' => 'danger'])->live()->required()->grouped()->options(['Returnable' => 'Returnable', 'Non-Returnable' => 'Non-Returnable']),
                        ToggleButtons::make('type')->default('Modification')->required()->grouped()->options(function (Get $get) {
                            if ($get('status') === "Returnable") {
                                return ['Modification' => 'Modification'];
                            } else {
                                return ['Personal Belonging' => 'Personal Belonging', 'Domestic Waste' => 'Domestic Waste', 'Construction Waste' => 'Construction Waste'];
                            }
                        }),
                        FileUpload::make('image')->label('Attached for all sections')->image()->imageEditor()->columnSpan(1),
                        FileUpload::make('supporting')->label('Attached Supporting Document')->image()->imageEditor()->columnSpan(1),                        Repeater::make('items')->required(function (Get $get){
                            if (!$get('itemsOut')){
                                return true;
                            }
                        })->label('Registered Asset')->addActionLabel('Add to Register Asset')->orderable(false)->schema([
                            Select::make('asset_id')
                                ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                ->live()->label('Asset')->options(function () {
                                    $data = [];
                                    $sub = AssetEmployeeItem::selectRaw('MAX(id) as id')
                                        ->whereHas('assetEmployee', function ($q) {
                                            $q->where('employee_id', getEmployee()->id);
                                        })
                                        ->groupBy('asset_id');

                                    $assetA=AssetEmployeeItem::query()
                                        ->whereIn('id', $sub)
                                        ->where('type', 'Assigned')->pluck('asset_id')->toArray();

                                    $assets = Asset::query()->with('product')->whereIn('id',$assetA)->whereHas('employees', function ($query) {
                                        return $query->whereHas('assetEmployee', function ($query) {
                                            return $query->where('employee_id', getEmployee()->id);
                                        });
                                    })->where('company_id', getCompany()->id)->get();
                                    foreach ($assets as $asset) {
                                        $data[$asset->id] = $asset->product?->title. " ( SKU #" . $asset->product?->sku . " )";
                                    }
                                    return $data;
                                })->required()->searchable()->preload(),
                            TextInput::make('remarks')->nullable()
                        ])->columnSpanFull()->columns(),
                        Repeater::make('itemsOut')->required(function (Get $get){
                            if (!$get('items')){
                                return true;
                            }
                        })->label('Unregistered Asset')->addActionLabel('Add to Unregister Asset')->orderable(false)->schema([
                            TextInput::make('name')->required(),
                            TextInput::make('quantity')->required(),
                            Select::make('unit')->searchable()->options(Unit::query()->where('company_id', getCompany()->id)->pluck('title','title'))->required(),
                            TextInput::make('remarks')->nullable(),
                            FileUpload::make('image')->columnSpanFull()->label('Image Upload')->image()->imageEditor(),
                        ])->columnSpanFull()->columns(4)
                    ])->columns(4)
                ])->slideOver()->modalWidth(MaxWidth::Full),
                Tables\Actions\Action::make('pdf')->url(fn($record) => route('pdf.takeOut', ['id' => $record->id]))->icon('heroicon-s-printer')->iconSize(IconSize::Large)->label('PDF'),
                Tables\Actions\ViewAction::make('view')->stickyModalHeader(false)->modalHeading('Gate Pass')->slideOver()->infolist([
                    Section::make([
                        TextEntry::make('employee.fullName'),
                        TextEntry::make('from'),
                        TextEntry::make('to'),
                        TextEntry::make('date')->date(),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('type')->badge(),
                        ImageEntry::make('media.original_url')->label('Attach Document & Supporting Document'  )->height(100),
                        RepeatableEntry::make('items')->label('Assets')->schema([
                            TextEntry::make('asset.description')->label('Asset Description'),
                            TextEntry::make('asset.number')->label('Asset Number'),
                            TextEntry::make('status')->color(fn ($state)=>match ($state){
                                'Approved'=>'success','Not Approved'=>'danger','Pending'=>'primary'
                            })->badge(),
                            TextEntry::make('remarks'),
                            TextEntry::make('returned_date'),
                        ])->columnSpanFull()->columns(4),
                        RepeatableEntry::make('itemsOut')->label('itemsOut')->schema([
                            TextEntry::make('name'),
                            TextEntry::make('quantity'),
                            TextEntry::make('status')->color(fn ($state)=>match ($state){
                                'Approved'=>'success','Not Approved'=>'danger','Pending'=>'primary'
                            })->badge(),
                            TextEntry::make('unit'),
                            TextEntry::make('remarks'),
                            ImageEntry::make('image')->height(100)
                        ])->columnSpanFull()->columns(),
                        \Filament\Infolists\Components\Section::make([
                            TextEntry::make('OutSide_date')->label('Outside Date')->dateTime(),
                            TextEntry::make('OutSide_comment')->label('Outside Comment '),
                        ])->columns(),
                        \Filament\Infolists\Components\Section::make([
                            TextEntry::make('InSide_date')->label('Inside Date')->dateTime(),
                            TextEntry::make('inSide_comment')->label('Inside Comment'),
                        ])->columns(),
                    ])->columns()
                ])->modalWidth(MaxWidth::Full),

            ])->filters([
                getFilterSubordinate()
            ]);
    }
}
