<?php

namespace App\Filament\Admin\Widgets;

use App\Filament\Admin\Resources\VisitorRequestResource;
use App\Filament\Admin\Resources\VisitorRequestResource\Pages\EditVisitorRequest;
use App\Models\VisitorRequest;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Carbon;

class VisitRequest extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table->defaultSort('id','desc')
            ->query(
                VisitorRequest::query()->where('company_id', getCompany()->id)->where('requested_by',getEmployee()->id)
                // ->where('status', '!=', 'FinishedCeo')
            )
            ->columns([
                Tables\Columns\TextColumn::make('#')->rowIndex(),

                Tables\Columns\TextColumn::make('visitors_detail')
                    ->label('Visitors')
                    ->state(fn($record) => implode(', ', (array_map(fn($item) => $item['name'], $record->visitors_detail))))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('visit_date')->label(' Date of Visit ')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('arrival_time')->time('H:m'),
                Tables\Columns\TextColumn::make('departure_time')->time('H:m'),
                Tables\Columns\TextColumn::make('InSide_date')->label('CheckIn ')->time('H:m'),
                Tables\Columns\TextColumn::make('OutSide_date')->label('Checkout ')->time('H:m'),
                Tables\Columns\TextColumn::make('status')->badge()->state((function ($record) {
                    switch ($record->status) {
                        case "approved":
                            return 'Approved';
                        case "Pending":
                            return 'Pending';
                        case "notApproved":
                            return 'Not Approved';
                    }
                }))->color(function ($state) {
                    switch ($state) {
                        case "Approved":
                            return 'success';
                        case "Pending":
                            return 'info';
                        case "Not Approved":
                            return 'danger';
                    }
                })->tooltip(fn($record)=>isset($record->approvals[0])? $record->approvals[0]->approve_date : false )->alignCenter(),

                Tables\Columns\TextColumn::make('employee.fullName')
                ->label('Requester')
                ->numeric()
                ->toggleable(isToggledHiddenByDefault: true)
                ->sortable(),
//                Tables\Columns\TextColumn::make('Time Leave')->state(function ($record) {
//
//                    $startTime = Carbon::make($record->InSide_date)->format('H:i:s');
//                    $endTime = $record->departure_time;
//                    if ($startTime and $endTime) {
//                        $difference = calculateTime($startTime, $endTime);
//                        return $difference;
//                    }
//                })->label('Time Leave'),
                Tables\Columns\TextColumn::make('Track Time')->state(function ($record) {
//                    $startTime = $record->InSide_date;
//                    $endTime = $record->OutSide_date;
//                    if ($startTime and $endTime) {
//                        $difference = calculateTime($startTime, $endTime);
//                        return $difference;
//                    }
                })->label('Track Time'),
            ])

            ->actions([
                Tables\Actions\DeleteAction::make()->visible(fn($record)=>$record->status==="Pending")->action(function ($record){
                    $record->approvals()->delete();
                    $record->delete();
                    Notification::make('success')->success()->title('Deleted')->send();
                }),
                Tables\Actions\ReplicateAction::make()->label('Duplicate')->modalHeading('Duplicate')->form(
                    [
                        Section::make('Visitor Access Request')->schema([
                            Section::make('Visit’s Details')->schema([
                                Select::make('agency')->options(getCompany()->agency)->createOptionForm([
                                    TextInput::make('title')->required()
                                ])->createOptionUsing(function ($data){
                                    $array = getCompany()->agency;
                                    if (isset($array)) {
                                        $array[$data['title']] = $data['title'];

                                    } else {
                                        $array = [$data['title'] => $data['title']];
                                    }
                                    getCompany()->update(['agency' => $array]);
                                    return $data['title'];
                                })->searchable()->preload(),
                                ToggleButtons::make('ICON')->label('ICON')->grouped()->boolean()->inline()->default(0)->required(),

                                DatePicker::make('visit_date')->label(' Date of Visit ')->default(now()->addDay())->required(),
                                TimePicker::make('arrival_time')->label('Arrival Time')->seconds(false)->before('departure_time')->required(),
                                TimePicker::make('departure_time')->label('Departure Time')->seconds(false)->after('arrival_time')->required(),
                                TextInput::make('purpose')->columnSpanFull()->required(),
                            ])->columns(5),
                            Repeater::make('visitors_detail')->addActionLabel('Add')->label('Visitors Detail')->schema([
                                TextInput::make('name')->label('Name')->required(),
                                TextInput::make('id')->label('ID/Passport')->required(),
                                TextInput::make('phone')->label('Phone'),
                                TextInput::make('organization')->label('Organization'),
                                Textarea::make('remarks')->columnSpan(3)->label('Remarks'),
                                ToggleButtons::make('type')->required()->grouped()->columnSpan(2)->label('Type')->options(['National' => 'National', 'International' => 'International', 'De-facto Security Forces' => 'De-facto Security Forces',]),
                                FileUpload::make('attachment')->downloadable()
                                    ->disk('public')->columnSpanFull(),

                            ])->columns(5)->columnSpanFull(),
                            Repeater::make('driver_vehicle_detail')
                                ->addActionLabel('Add')
                                ->label('Drivers/Vehicles Detail')->schema([
                                    TextInput::make('name')->label('Full Name')->required(),
                                    TextInput::make('id')->label('ID/Passport')->required(),
                                    TextInput::make('phone')->label('Phone'),
                                    Select::make('model')->options(getCompany()->visitrequest_model)->createOptionForm([
                                        TextInput::make('title')->required()
                                    ])->createOptionUsing(function ($data){
                                        $array=getCompany()->visitrequest_model;
                                        if (isset($array)){
                                            $array[$data['title']]=$data['title'];

                                        }else{
                                            $array=[$data['title']=>$data['title']];
                                        }
                                        getCompany()->update(['visitrequest_model'=>$array]);
                                        return $data['title'];
                                    })->searchable()->preload(),
                                    Select::make('color')
                                        ->options(
                                            collect(getCompany()->visitrequest_color)
                                                ->mapWithKeys(fn($color, $title) => [
                                                    $title => "<div style='display:flex;align-items:center;gap:8px;'>
                              <span style='display:inline-block;width:12px;height:12px;background-color:$color;border-radius:50%;'></span>
                              $title
                          </div>"
                                                ])
                                                ->toArray()
                                        )
                                        ->createOptionForm([
                                            TextInput::make('title')->required(),
                                            ColorPicker::make('color')->required()
                                        ])
                                        ->createOptionUsing(function ($data) {
                                            $array = getCompany()->visitrequest_color ?? [];
                                            $array[$data['title']] = $data['color'];
                                            getCompany()->update(['visitrequest_color' => $array]);
                                            return $data['title'];
                                        })->allowHtml()
                                        ->searchable()
                                        ->preload()
                                        ->label('Color')
                                    ,
                                    TextInput::make('Registration_Plate')->required(),
                                ])->columns(3)->columnSpanFull(),
                        ])->columns(2)
                    ]
                )->action(function ($data){
                    $visitorRequest = VisitorRequest::query()->create([
                        'visit_date'=>$data['visit_date'],
                        'arrival_time'=>$data['arrival_time'],
                        'departure_time'=>$data['departure_time'],
                        'purpose'=>$data['purpose'],
                        'ICON'=>$data['ICON'],
                        'visitors_detail'=>$data['visitors_detail'],
                        'driver_vehicle_detail'=>$data['driver_vehicle_detail'],
                        'requested_by'=>getEmployee()->id,
                        'company_id'=>getCompany()->id,
                    ]);

                    // sendAR(getEmployee(),,getCompany());
                    sendSecurity($visitorRequest, getCompany());
                    // sendSecurity(getEmployee(),$visitorRequest,getCompany());
                    Notification::make('success')->color('success')->success()->title('Request Sent')->send()->sendToDatabase(auth()->user());

                })->modalWidth(MaxWidth::Full),
                EditAction::make()->visible(fn($record)=>$record->status=='Pending')->form(VisitorRequestResource::getForm())->modalWidth(MaxWidth::Full),
            ])
            ->headerActions([
                Action::make('Visit Request')->label('New Visit Request')->modalWidth(MaxWidth::Full)->form(
                    [
                        Section::make('Visitor Access Request')->schema([
                            Section::make('Visit’s Details')->schema([
                                Select::make('agency')->options(getCompany()->agency)->createOptionForm([
                                    TextInput::make('title')->required()
                                ])->createOptionUsing(function ($data){
                                    $array = getCompany()->agency;
                                    if (isset($array)) {
                                        $array[$data['title']] = $data['title'];

                                    } else {
                                        $array = [$data['title'] => $data['title']];
                                    }
                                    getCompany()->update(['agency' => $array]);
                                    return $data['title'];
                                })->searchable()->preload(),
                                ToggleButtons::make('ICON')->label('ICON')->grouped()->boolean()->inline()->default(0)->required(),

                                DatePicker::make('visit_date')->label('Visit Date')->default(now()->addDay())->required(),
                                TimePicker::make('arrival_time')->label('Arrival Time')->seconds(false)->before('departure_time')->required(),
                                TimePicker::make('departure_time')->label('Departure Time')->seconds(false)->after('arrival_time')->required(),
                                TextInput::make('purpose')->columnSpanFull()->required(),
                            ])->columns(5),
                            Repeater::make('visitors_detail')->addActionLabel('Add')->label('Visitors Detail')->schema([
                                TextInput::make('name')->label('Name')->required(),
                                TextInput::make('id')->label('ID/Passport')->required(),
                                TextInput::make('phone')->label('Phone'),
                                TextInput::make('organization')->label('Organization'),
                                Textarea::make('remarks')->columnSpan(3)->label('Remarks'),
                                ToggleButtons::make('type')->required()->grouped()->columnSpan(2)->label('Type')->options(['National' => 'National', 'International' => 'International', 'De-facto Security Forces' => 'De-facto Security Forces',]),
                                FileUpload::make('attachment')->downloadable()
                                    ->disk('public')->columnSpanFull(),

                            ])->columns(5)->columnSpanFull(),
                            Repeater::make('driver_vehicle_detail')
                                ->addActionLabel('Add')
                                ->label('Drivers/Vehicles Detail')->schema([
                                    TextInput::make('name')->label('Full Name')->required(),
                                    TextInput::make('id')->label('ID/Passport')->required(),
                                    TextInput::make('phone')->label('Phone'),
                                    Select::make('model')->options(getCompany()->visitrequest_model)->createOptionForm([
                                        TextInput::make('title')->required()
                                    ])->createOptionUsing(function ($data){
                                        $array=getCompany()->visitrequest_model;
                                        if (isset($array)){
                                            $array[$data['title']]=$data['title'];

                                        }else{
                                            $array=[$data['title']=>$data['title']];
                                        }
                                        getCompany()->update(['visitrequest_model'=>$array]);
                                        return $data['title'];
                                    })->searchable()->preload(),
                                    Select::make('color')
                                        ->options(
                                            collect(getCompany()->visitrequest_color)
                                                ->mapWithKeys(fn($color, $title) => [
                                                    $title => "<div style='display:flex;align-items:center;gap:8px;'>
                              <span style='display:inline-block;width:12px;height:12px;background-color:$color;border-radius:50%;'></span>
                              $title
                          </div>"
                                                ])
                                                ->toArray()
                                        )
                                        ->createOptionForm([
                                            TextInput::make('title')->required(),
                                            ColorPicker::make('color')->required()
                                        ])
                                        ->createOptionUsing(function ($data) {
                                            $array = getCompany()->visitrequest_color ?? [];
                                            $array[$data['title']] = $data['color'];
                                            getCompany()->update(['visitrequest_color' => $array]);
                                            return $data['title'];
                                        })->allowHtml()
                                        ->searchable()
                                        ->preload()
                                        ->label('Color')
                                    ,
                                    TextInput::make('Registration_Plate')->required(),
                                ])->columns(3)->columnSpanFull(),
                        ])->columns(2)
                    ]
                )->action(function (array $data): void {

                   $visitorRequest = VisitorRequest::query()->create([
                        'visit_date'=>$data['visit_date'],
                        'arrival_time'=>$data['arrival_time'],
                        'departure_time'=>$data['departure_time'],
                        'purpose'=>$data['purpose'],
                        'ICON'=>$data['ICON'],
                        'visitors_detail'=>$data['visitors_detail'],
                        'driver_vehicle_detail'=>$data['driver_vehicle_detail'],
                        'requested_by'=>getEmployee()->id,
                        'company_id'=>getCompany()->id,
                    ]);
                    // sendAR(getEmployee(),,getCompany());
                    sendSecurity($visitorRequest, getCompany());
                    // sendSecurity(getEmployee(),$visitorRequest,getCompany());
                    Notification::make('success')->color('success')->success()->title('Request Sent')->send()->sendToDatabase(auth()->user());

                })->label('Visitor Access Request')
            ])
            ->bulkActions([])
        ;
    }
    public static function getPages(): array
    {
        return [

            'edit' => EditVisitorRequest::route('/{record}/edit'),
        ];
    }
}
