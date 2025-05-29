<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Asset;
use App\Models\Unit;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Get;
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

class TakeOut extends BaseWidget
{

    protected int|string|array $columnSpan = 'full';

protected static ?string $heading='Gate Pass';
    public function table(Table $table): Table
    {
        return $table->emptyStateHeading('No Gate Pass')->heading('Gate Pass')->headerActions([
            Action::make('Take Out')->label('New Gate Pass')->form([
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
                    Repeater::make('items')->required(function (Get $get){
                        if (!$get('itemsOut')){
                            return true;
                        }
                    })->label('Registered Asset')->addActionLabel('Add to Register Asset')->orderable(false)->schema([
                        Select::make('asset_id')
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                            ->live()->label('Asset')->options(function () {
                                $data = [];
                                $assets = Asset::query()->with('product')->whereHas('employees', function ($query) {
                                    return $query->where('return_date', null)->where('return_approval_date', null)->whereHas('assetEmployee', function ($query) {
                                        return $query->where('employee_id', getEmployee()->id);
                                    });
                                })->where('company_id', getCompany()->id)->get();
                                foreach ($assets as $asset) {
                                    $data[$asset->id] = $asset->product?->title . " ( SKU #" . $asset->product?->sku . " )";
                                }
                                return $data;
                            })->required()->searchable()->preload(),
                        TextInput::make('remarks')->nullable()
                    ])->columnSpanFull()->columns(),
                    Repeater::make('itemsOut')->required(function (Get $get){
                        if (!$get('items')){
                            return true;
                        }
                    })->label('Unregistered Asset')->addActionLabel('Add to UnRegister Asset')->orderable(false)->schema([
                        TextInput::make('name')->required(),
                        TextInput::make('quantity')->required(),
                        Select::make('unit')->searchable()->options(Unit::query()->where('company_id', getCompany()->id)->pluck('title','title'))->required(),
                        TextInput::make('remarks')->nullable(),
                    ])->columnSpanFull()->columns(4)
                ])->columns(4)

            ])->modalWidth(MaxWidth::SixExtraLarge)->action(function ($data) {
                $id = getCompany()->id;
                $data['company_id'] = $id;
                $employee = getEmployee();

                $data['employee_id'] = $employee->id;
                $items = $data['items'];
                unset($data['items']);
                $takeOut = \App\Models\TakeOut::query()->create($data);
                foreach ($items as $item) {
                    $item['company_id'] = $id;
                    $takeOut->items()->create($item);
                }
                sendAR($employee,$takeOut,getCompany());
                Notification::make('success')->color('success')->success()->title('Request Sent')->send()->sendToDatabase(auth()->user());
            })->color('warning')
        ])
            ->query(
                \App\Models\TakeOut::query()->where('employee_id', getEmployee()->id)->orderBy('id','desc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
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
            ])->actions([
                Tables\Actions\Action::make('pdf')->url(fn($record) => route('pdf.takeOut', ['id' => $record->id]))->icon('heroicon-s-printer')->iconSize(IconSize::Large)->label('PDF'),
                Tables\Actions\ViewAction::make('view')->infolist([
                    Section::make([
                        TextEntry::make('employee.fullName'),
                        TextEntry::make('from'),
                        TextEntry::make('to'),
                        TextEntry::make('date')->date(),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('type')->badge(),
                        RepeatableEntry::make('items')->label('Assets')->schema([
                            TextEntry::make('asset.title'),
                            TextEntry::make('remarks'),
                            TextEntry::make('returned_date'),
                        ])->columnSpanFull()->columns(3),
                        RepeatableEntry::make('itemsOut')->label('itemsOut')->schema([
                            TextEntry::make('name'),
                            TextEntry::make('remarks'),
                               TextEntry::make('quantity'),
                            TextEntry::make('unit'),
                        ])->columnSpanFull()->columns(),
                        \Filament\Infolists\Components\Section::make([
                            TextEntry::make('OutSide_date')->dateTime(),
                            TextEntry::make('OutSide_comment'),
                        ])->columns(),
                        \Filament\Infolists\Components\Section::make([
                            TextEntry::make('InSide_date')->dateTime(),
                            TextEntry::make('inSide_comment'),
                        ])->columns(),
                    ])->columns()
                ]),

            ]);
    }
}
