<?php

namespace App\Filament\Admin\Widgets;

use App\Filament\Admin\Resources\VisitorRequestResource;
use App\Filament\Admin\Resources\VisitorRequestResource\Pages\EditVisitorRequest;
use App\Models\VisitorRequest;
use Carbon\Carbon;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Support\Enums\IconSize;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;

class VisitRequest extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = 'Visitor  Request';
    public function table(Table $table): Table
    {
        return $table->emptyStateHeading('No Visitor  Request')->defaultSort('id', 'desc')
            ->query(
                VisitorRequest::query()->where('company_id', getCompany()->id)
            )
            ->columns([
                Tables\Columns\TextColumn::make('')->label('No')->rowIndex(),
                Tables\Columns\TextColumn::make('employee.fullName')->label('Employee'),
                Tables\Columns\TextColumn::make('SN_code')->label('Department Code '),
                Tables\Columns\TextColumn::make('visitors_detail')->label('Visitors')->state(fn($record) => implode(', ', (array_map(fn($item) => $item['name'], $record->visitors_detail))))->sortable(),
                Tables\Columns\TextColumn::make('visiting_dates')->limitList(5)->bulleted()->label('Scheduled Visit Dates')->sortable(),
                Tables\Columns\TextColumn::make('arrival_time')->time('H:i A'),
                Tables\Columns\TextColumn::make('departure_time')->time('H:i A'),
                Tables\Columns\TextColumn::make('approvals.approve_date')->label('Approval Date'),
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
                })->alignCenter(),
                Tables\Columns\TextColumn::make('approvals.comment')->label('Comments')
            ])

            ->actions([
                Tables\Actions\Action::make('pdf')->tooltip('Print')->icon('heroicon-s-printer')->iconSize(IconSize::Medium)->label('')
                    ->url(fn($record) => route('pdf.requestVisit', ['id' => $record->id]))->openUrlInNewTab(),
                Tables\Actions\DeleteAction::make()->visible(fn($record) => $record->status === "Pending")->action(function ($record) {
                    $record->approvals()->delete();
                    $record->delete();
                    Notification::make('success')->success()->title('Deleted')->send();
                }),
                Tables\Actions\ReplicateAction::make()->label('Duplicate')->modalSubmitActionLabel('Duplicate')->modalHeading('Duplicate')->form(
                    [
                        Section::make('Visitor Access Details')->schema([
                            Section::make('')->schema([
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

                                TimePicker::make('arrival_time')->label('Arrival Time')->seconds(false)->before('departure_time')->required(),
                                TimePicker::make('departure_time')->label('Departure Time')->seconds(false)->after('arrival_time')->required(),
                                DatePicker::make('visit_date')->label('Visit Date')->default(now()->addDay())->hintActions([
                                    \Filament\Forms\Components\Actions\Action::make('te')->label('Select Daily')->action(function (Get $get,Set $set){
                                        $dates=$get('visiting_dates');
                                        if ($get('visit_date')){
                                            try {
                                                $dates[]= Carbon::createFromFormat('Y-m-d', $get('visit_date'))->format('d/m/Y') ;
                                                $set('visiting_dates',$dates);
                                            } catch (\Exception $e ){

                                            }
                                        }
                                        $set('visit_date',null);
                                    }),\Filament\Forms\Components\Actions\Action::make('Add')->label('Select Monthly')->form([
                                        DateRangePicker::make('date')
                                    ])->action(function (Set $set,$data){
                                        $dataDate=explode(' -',$data['date']);
                                        $start = Carbon::createFromFormat('d/m/Y', $dataDate[0]);
                                        $end = Carbon::createFromFormat('d/m/Y', trim($dataDate[1]));
                                        $dates = collect();
                                        while ($start->lte($end)) {
                                            $dates->push($start->copy()->format('d/m/Y'));
                                            $start->addDay();
                                        }
                                        $set('visiting_dates',$dates->toArray());
                                    })
                                ]),
                                Select::make('visiting_dates')->required()->columnSpan(3)->label('Scheduled Visit Dates')->multiple(),                                TextInput::make('purpose')->columnSpanFull()->required(),
                            ])->columns(4),
                            Repeater::make('visitors_detail')->addActionLabel('Add')->label('Visitors Detail')->schema([
                                TextInput::make('name')->label('Name')->required(),
                                TextInput::make('id')->label('ID/Passport')->required(),
                                TextInput::make('phone')->label('Phone'),
                                TextInput::make('organization')->label('Organization'),
                                TextInput::make('remarks')->label('Remarks'),
                                FileUpload::make('attachment')->downloadable()
                                    ->disk('public')->columnSpanFull(),
                            ])->columns(5)->columnSpanFull(),
                            Section::make([
                                Repeater::make('armed')->grid(3)->label('Armed Close Protection Officers (If Applicable)')->columnSpanFull()->schema([
                                    Select::make('type')->columnSpan(2)->searchable()->disableOptionsWhenSelectedInSiblingRepeaterItems()->required()->columns(2)->label(' ')->options(['National' => 'National', 'International' => 'International', 'De-facto Security Forces' => 'De-facto Security Forces',]),
                                    TextInput::make('total')->numeric()->required()
                                ])->maxItems(3)->columns(3)->default(function () {
                                    return [
                                        ['type' => 'National', 'total' => 0],
                                        ['type' => 'International', 'total' => 0],
                                        ['type' => 'De-facto Security Forces', 'total' => 0],
                                    ];
                                })->minItems(3)->reorderableWithDragAndDrop(false)
                            ]),
                            Repeater::make('driver_vehicle_detail')
                                ->addActionLabel('Add')
                                ->label('Drivers/Vehicles Detail')->schema([
                                    TextInput::make('name')->label('Full Name')->required(),
                                    TextInput::make('id')->label('ID/Passport')->required(),
                                    TextInput::make('phone')->label('Phone'),
                                    Select::make('model')->options(getCompany()->visitrequest_model)->createOptionForm([
                                        TextInput::make('title')->required()
                                    ])->createOptionUsing(function ($data) {
                                        $array = getCompany()->visitrequest_model;
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
                                    TextInput::make('trip')->required()->numeric(),
                                    FileUpload::make('driver')->label('Driver National Identification Card')->imageEditor()->image()->columnSpan(3),
                                    FileUpload::make('image')->label('Vehicle Number Plate Photo')->imageEditor()->image()->columnSpan(4),
                                ])->columns(7)->columnSpanFull(),
                        ])->columns(2)
                    ]
                )->action(function ($data){
                    $employee=getEmployee();
                    $abr=$employee->department->abbreviation;
                    $lastRecord=VisitorRequest::query()->whereIn('requested_by',$employee->department->employees()->pluck('id'))->latest()->first();

                    if ($lastRecord){
                        $code=getNextCodeVisit($lastRecord->SN_code,$abr);
                    }else{
                        $code=$abr."/00001";
                    }
                    $visitorRequest = VisitorRequest::query()->create([
                        'visit_date'=>$data['visit_date'],
                        'arrival_time'=>$data['arrival_time'],
                        'departure_time'=>$data['departure_time'],
                        'purpose'=>$data['purpose'],
                        'ICON'=>$data['ICON'],
                        'visitors_detail'=>$data['visitors_detail'],
                        'driver_vehicle_detail'=>$data['driver_vehicle_detail'],
                        'requested_by'=>$employee->id,
                        'company_id'=>getCompany()->id,
                        'armed' => $data['armed'],
                        'SN_code'=>$code,
                        'visiting_dates'=>$data['visiting_dates']

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
                        Section::make('Visitor Access Details')->schema([
                            Section::make('')->schema([
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

                                TimePicker::make('arrival_time')->label('Arrival Time')->seconds(false)->before('departure_time')->required(),
                                TimePicker::make('departure_time')->label('Departure Time')->seconds(false)->after('arrival_time')->required(),
                                DatePicker::make('visit_date')->label('Visit Date')->default(now()->addDay())->hintActions([
                                    \Filament\Forms\Components\Actions\Action::make('te')->label('Select Daily')->action(function (Get $get,Set $set){
                                        $dates=$get('visiting_dates');
                                        if ($get('visit_date')){
                                            try {
                                                $dates[]= Carbon::createFromFormat('Y-m-d', $get('visit_date'))->format('d/m/Y') ;
                                                $set('visiting_dates',$dates);
                                            } catch (\Exception $e ){

                                            }
                                        }
                                        $set('visit_date',null);
                                    }),\Filament\Forms\Components\Actions\Action::make('Add')->label('Select Monthly')->form([
                                        DateRangePicker::make('date')
                                    ])->action(function (Set $set,$data){
                                        $dataDate=explode(' -',$data['date']);
                                        $start = Carbon::createFromFormat('d/m/Y', $dataDate[0]);
                                        $end = Carbon::createFromFormat('d/m/Y', trim($dataDate[1]));
                                        $dates = collect();
                                        while ($start->lte($end)) {
                                            $dates->push($start->copy()->format('d/m/Y'));
                                            $start->addDay();
                                        }
                                        $set('visiting_dates',$dates->toArray());
                                    })
                                ]),
                                Select::make('visiting_dates')->required()->columnSpan(3)->label('Scheduled Visit Dates')->multiple(),                                  TextInput::make('purpose')->columnSpanFull()->required(),
                            ])->columns(4),
                            Repeater::make('visitors_detail')->addActionLabel('Add')->label('Visitors Detail')->schema([
                                TextInput::make('name')->label('Name')->required(),
                                TextInput::make('id')->label('ID/Passport')->required(),
                                TextInput::make('phone')->label('Phone'),
                                TextInput::make('organization')->label('Organization'),
                                TextInput::make('remarks')->label('Remarks'),
                                FileUpload::make('attachment')->downloadable()->disk('public')->columnSpanFull(),
                            ])->columns(5)->columnSpanFull(),
                            Section::make([
                                Repeater::make('armed')->grid(3)->label('Armed Close Protection Officers (If Applicable)')->columnSpanFull()->schema([
                                    Select::make('type')->columnSpan(2)->searchable()->disableOptionsWhenSelectedInSiblingRepeaterItems()->required()->columns(2)->label(' ')->options(['National' => 'National', 'International' => 'International', 'De-facto Security Forces' => 'De-facto Security Forces',]),
                                    TextInput::make('total')->numeric()->required()
                                ])->maxItems(3)->columns(3)->default(function () {
                                    return [
                                        ['type' => 'National', 'total' => 0],
                                        ['type' => 'International', 'total' => 0],
                                        ['type' => 'De-facto Security Forces', 'total' => 0],
                                    ];
                                })->minItems(3)->reorderableWithDragAndDrop(false)
                            ]),
                            Repeater::make('driver_vehicle_detail')
                                ->addActionLabel('Add')
                                ->label('Drivers/Vehicles Detail')->schema([
                                    TextInput::make('name')->label('Full Name')->required(),
                                    TextInput::make('id')->label('ID/Passport')->required(),
                                    TextInput::make('phone')->label('Phone'),
                                    Select::make('model')->options(getCompany()->visitrequest_model)->createOptionForm([
                                        TextInput::make('title')->required()
                                    ])->createOptionUsing(function ($data) {
                                        $array = getCompany()->visitrequest_model;
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
                                    TextInput::make('trip')->required()->numeric(),
                                    FileUpload::make('driver')->label('Driver National Identification Card')->imageEditor()->image()->columnSpan(3),
                                    FileUpload::make('image')->label('Vehicle Number Plate Photo')->imageEditor()->image()->columnSpan(4),
                                ])->columns(7)->columnSpanFull(),
                        ])->columns(2)
                    ]
                )->action(function (array $data): void {
                    $employee=getEmployee();
                    $abr=$employee->department->abbreviation;
                    $lastRecord=VisitorRequest::query()->whereIn('requested_by',$employee->department->employees()->pluck('id'))->latest()->first();
                    if ($lastRecord){
                        $code=getNextCodeVisit($lastRecord->SN_code,$abr);
                    }else{
                        $code=$abr."/00001";
                    }
                    $visitorRequest = VisitorRequest::query()->create([
                        'visit_date'=>$data['visit_date'],
                        'arrival_time'=>$data['arrival_time'],
                        'departure_time'=>$data['departure_time'],
                        'purpose'=>$data['purpose'],
                        'ICON'=>$data['ICON'],
                        'visitors_detail'=>$data['visitors_detail'],
                        'driver_vehicle_detail'=>$data['driver_vehicle_detail'],
                        'requested_by'=>$employee->id,
                        'company_id'=>getCompany()->id,
                        'armed' => $data['armed'],
                        'SN_code'=>$code,
                        'visiting_dates'=>$data['visiting_dates']
                    ]);
                    // sendAR(getEmployee(),,getCompany());
                    sendSecurity($visitorRequest, getCompany());
                    // sendSecurity(getEmployee(),$visitorRequest,getCompany());
                    Notification::make('success')->color('success')->success()->title('Request Sent')->send()->sendToDatabase(auth()->user());

                })->label('Visitor Access Request'),

            ])
            ->bulkActions([

            ])->filters([
                Tables\Filters\TernaryFilter::make('All')->label('Data Filter ')
                    ->placeholder('Only Me')->searchable()
                    ->trueLabel('All Subordinates')
                    ->falseLabel('Only Me')
                    ->queries(
                        true: fn (Builder $query) => $query->whereIn('requested_by',getSubordinate()),
                        false: fn (Builder $query) => $query->where('requested_by', getEmployee()->id),
                        blank: fn (Builder $query) => $query->where('requested_by', getEmployee()->id),
                    )
            ])
        ;
    }
    public static function getPages(): array
    {
        return [

            'edit' => EditVisitorRequest::route('/{record}/edit'),
        ];
    }
}
