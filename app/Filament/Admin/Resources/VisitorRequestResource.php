<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\VisitorRequestResource\Pages;
use App\Filament\Admin\Resources\VisitorRequestResource\RelationManagers;
use App\Models\VisitorRequest;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconSize;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class VisitorRequestResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = VisitorRequest::class;
    protected static ?string $navigationLabel = 'Visitor Access Request';
    protected static ?string $navigationGroup = 'Security Management';
    protected static ?int $navigationSort = 100;
    protected static ?string $navigationIcon = 'heroicon-o-eye';

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'reception',
            'logo_and_name',
            'security',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Visitor Access Request')->schema([
                    Section::make('')->schema([
                        Forms\Components\Select::make('requested_by')->live()
                            ->searchable()
                            ->preload()
                            ->required()
                            ->options(getCompany()->employees->pluck('fullName', 'id'))
                            ->default(fn() => auth()->user()->employee->id),

                        Select::make('agency')->options(getCompany()->agency)->createOptionForm([
                            Forms\Components\TextInput::make('title')->required()
                        ])->createOptionUsing(function ($data) {
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

                        Forms\Components\TimePicker::make('arrival_time')->label('Arrival Time')->seconds(false)->before('departure_time')->required(),
                        Forms\Components\TimePicker::make('departure_time')->label('Departure Time')->seconds(false)->after('arrival_time')->required(),
                        Forms\Components\DatePicker::make('visit_date')->live()->label('Visit Date')->default(now()->addDay())->hintActions([
                            Forms\Components\Actions\Action::make('te')->label('Select Daily')->action(function (Forms\Get $get,Forms\Set $set){
                                $dates=$get('visiting_dates');
                                if ($get('visit_date')){
                                    $dates[]= Carbon::createFromFormat('Y-m-d', $get('visit_date'))->format('d/m/Y') ;
                                    $set('visiting_dates',$dates);
                                }
                                $set('visit_date',null);
                            }),Forms\Components\Actions\Action::make('Add')->label('Select Monthly')->form([
                                DateRangePicker::make('date')
                            ])->action(function (Forms\Get $get,Forms\Set $set,$data){
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
                        Select::make('visiting_dates')->required()->columnSpan(4)->label('Scheduled Visit Dates')->multiple(),
                        Forms\Components\TextInput::make('purpose')->columnSpanFull()->required(),
                    ])->columns(5),
                    Forms\Components\Repeater::make('visitors_detail')
                        ->addActionLabel('Add ')
                        ->label('Visitors Details')
                        ->schema([
                            Forms\Components\TextInput::make('name')->label(' Name')->required(),
                            Forms\Components\TextInput::make('id')->label('ID/Passport')->required(),
                            Forms\Components\TextInput::make('phone')->label('Phone'),
                            Forms\Components\TextInput::make('organization')->label('Organization'),
                            Forms\Components\TextInput::make('remarks')->label('Remarks'),
                            FileUpload::make('attachment')->downloadable()
                                ->disk('public')->columnSpanFull(),
                        ])->columns(5)->columnSpanFull(),
                    Section::make([
                        Forms\Components\Repeater::make('armed')->grid(3)->label('Armed Close Protection Officers (If Applicable)')->columnSpanFull()->schema([
                            Forms\Components\Select::make('type')->searchable()->disableOptionsWhenSelectedInSiblingRepeaterItems()->required()->columns(2)->label(' ')->options(['National' => 'National', 'International' => 'International', 'De-facto Security Forces' => 'De-facto Security Forces',]),
                            Forms\Components\TextInput::make('total')->numeric()->required()
                        ])->maxItems(3)->columns(2)->default(function () {
                            return [
                                ['type' => 'National', 'total' => 0],
                                ['type' => 'International', 'total' => 0],
                                ['type' => 'De-facto Security Forces', 'total' => 0],
                            ];
                        })->minItems(3)
                    ]),
                    Forms\Components\Repeater::make('driver_vehicle_detail')
                        ->addActionLabel('Add')
                        ->label('Drivers/Vehicles Detail')->schema([
                            Forms\Components\TextInput::make('name')->label('Full Name')->required(),
                            Forms\Components\TextInput::make('id')->label('ID/Passport')->required(),
                            Forms\Components\TextInput::make('phone')->label('Phone'),
                            Select::make('model')->options(getCompany()->visitrequest_model)->createOptionForm([
                                Forms\Components\TextInput::make('title')->required()
                            ])->createOptionUsing(function ($data) {
                                $array = getCompany()->visitrequest_model;
                                if (isset($array)) {
                                    $array[$data['title']] = $data['title'];
                                } else {
                                    $array = [$data['title'] => $data['title']];
                                }
                                getCompany()->update(['visitrequest_model' => $array]);
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
                                    Forms\Components\TextInput::make('title')->required(),
                                    Forms\Components\ColorPicker::make('color')->required()
                                ])
                                ->createOptionUsing(function ($data) {
                                    $array = getCompany()->visitrequest_color ?? [];
                                    $array[$data['title']] = $data['color'];
                                    getCompany()->update(['visitrequest_color' => $array]);
                                    return $data['title'];
                                })->allowHtml()
                                ->searchable()
                                ->preload()
                                ->label('Color'),
                            Forms\Components\TextInput::make('Registration_Plate')->required(),
                            Forms\Components\TextInput::make('trip')->required(),
                            FileUpload::make('driver')->label('Driver National Identification Card')->imageEditor()->image()->columnSpan(3),
                            FileUpload::make('image')->label('Vehicle Number Plate Photo')->imageEditor()->image()->columnSpan(4),

                        ])->columns(7)->columnSpanFull(),
                    Forms\Components\Hidden::make('company_id')
                        ->default(getCompany()->id)
                        ->required(),

                ])->columns(2),


            ]);
    }

    public static function table(Table $table): Table
    {

        return $table->defaultSort('id', 'desc')->headerActions([
            ExportAction::make('export')->after(function (){
                if (Auth::check()) {
                    activity()
                        ->causedBy(Auth::user())
                        ->withProperties([
                            'action' => 'export',
                        ])
                        ->log('Export' . 'Visitor Access Request');
                }
            })->exports([
                ExcelExport::make()->askForFilename('Visitor Form')->withColumns([
                    Column::make('employee.fullName'),
                    Column::make('visit_date'),
                    Column::make('arrival_time'),
                    Column::make('departure_time'),
                    Column::make('agency'),
                    Column::make('purpose'),
                    Column::make('visitors_detail')->formatStateUsing(function ($state) {
                        if (!is_array($state)) {
                            return '-';
                        }
                        $i = 0;
                        return collect($state['name'])->map(fn($item, $index) => ($i + 1) . ") " .
                            "Name: {$state['name']}, " .
                            "ID: {$state['id']}, " .
                            "Phone: {$state['phone']}, " .
                            "Organization: {$state['organization']}, " .
                            "Remarks: {$state['remarks']}")->implode("\n");
                    }),
                    Column::make('driver_vehicle_detail')->formatStateUsing(function ($state) {
                        if (!is_array($state)) {
                            return '-';
                        }
                        $i = 0;

                        return collect($state['name'])->map(fn($item, $index) => ($i + 1) . ") " .
                            "Name: {$state['name']}, " .
                            "ID: {$state['id']}, " .
                            "Phone: {$state['phone']}, " .
                            "Model: {$state['model']}, " .
                            "Color: {$state['color']}, " .
                            "Plate: {$state['Registration_Plate']}")->implode("\n");
                    }),
                    Column::make('approval_date'),
                    Column::make('status'),
                    Column::make('armed'),
                    Column::make('gate_status'),
                    Column::make('InSide_date'),
                    Column::make('OutSide_date'),
                    Column::make('inSide_comment'),
                    Column::make('OutSide_comment'),
                    Column::make('employee.fullName'),
                    Column::make('created_at'),
                ]),
            ])->label('Export Visitor Requests')->color('purple')
        ])
            ->columns([

                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('SN_code')->label('Department Code'),
                Tables\Columns\TextColumn::make('employee.fullName')->label('Requester')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('visitors_detail')->label('Visitors')->state(fn($record) => array_map(fn($item) => $item['name'], $record->visitors_detail))->numeric()->sortable()->badge()->limitList(1),
                Tables\Columns\TextColumn::make('visiting_dates')->limitList(5)->bulleted()->label('Scheduled Visit Dates')->sortable(),
                Tables\Columns\TextColumn::make('arrival_time')->time('H:i A'),
                Tables\Columns\TextColumn::make('departure_time')->time('H:i A'),
                Tables\Columns\TextColumn::make('InSide_date')->label('Check IN ')->time('h:i A'),
                Tables\Columns\TextColumn::make('OutSide_date')->label('Check OUT ')->time(),
                Tables\Columns\TextColumn::make('Track Time')->state(function ($record) {
                    $startTime = $record->InSide_date;
                    $endTime = $record->OutSide_date;
                    if ($startTime and $endTime) {
                        return  diffVisit($startTime, $endTime);
                    }
                })->label('Track Time'),
                Tables\Columns\TextColumn::make('status')->label('Head of Security ')->tooltip(fn($record)=>isset($record->approvals[0])? $record->approvals[0]->approve_date : false )->alignCenter()->state(fn($record)=>match ($record->status){
                    'approved'=>'Approved',
                    'Pending'=>'Pending',
                    'notApproved'=>'Not Approved',
                    'default'=>''
                })->color(function ($state) {
                    switch ($state) {
                        case "Approved":
                            return 'success';
                        case "Pending":
                            return 'info';
                        case "Not Approved":
                            return 'danger';
                    }
                })->badge(),
                Tables\Columns\TextColumn::make('gate_status')->state(fn($record)=>match ($record->gate_status){
                    'CheckedOut'=>'Checked OUT',
                    'CheckedIn'=>'Checked IN',
                    default=>''
                })->label('Reception')->badge(),
                Tables\Columns\ToggleColumn::make('ICON')->label("ICON")->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                DateRangeFilter::make('visit_date'),
                Tables\Filters\SelectFilter::make('department')->searchable()->preload()->label('Department')->options(getCompany()->departments()->pluck('title','id'))->query(fn($query,$data)=>isset($data['value'])? $query->whereHas('employee',function ($query)use($data){
                    return $query->where('department_id',$data['value']);
                }):$query),
                Tables\Filters\SelectFilter::make('requested_by')->options(getCompany()->employees->pluck('info', 'id'))->searchable()->preload()->label('Employee'),
                DateRangeFilter::make('visit_date')->label('Visit Date'),
                Tables\Filters\SelectFilter::make('status')->options(['approved' => 'approved', 'notApproved' => 'notApproved'])->searchable()
            ], getModelFilter())
            ->actions([
                Tables\Actions\Action::make('ActionInSide')->label('Check IN ')->form([
                    Forms\Components\DateTimePicker::make('InSide_date')->withoutSeconds()->label(' Date And Time')->required()->default(now()),
                    Forms\Components\Textarea::make('inSide_comment')->label(' Comment')
                ])->requiresConfirmation()->action(function ($data, $record) {
                    $record->update(['InSide_date' => $data['InSide_date'], 'inSide_comment' => $data['inSide_comment'], 'gate_status' => 'CheckedIn']);
                    Notification::make('success')->success()->title('Submitted Successfully')->send();
                })->visible(function ($record) {
                    if (auth()->user()->can('reception_visitor::request')) {
                        if ($record->status === "approved") {
                            if ($record->InSide_date === null) {
                                return true;
                            }
                        }
                    }
                    return false;
                }),
                Tables\Actions\Action::make('ActionOutSide')->label('Check OUT')->form([
                    Forms\Components\DateTimePicker::make('OutSide_date')->withoutSeconds()->label(' Date And Time')->required()->default(now()),
                    Forms\Components\Textarea::make('OutSide_comment')->label(' Comment')
                ])->requiresConfirmation()->action(function ($data, $record) {
                    $record->update(['OutSide_date' => $data['OutSide_date'], 'OutSide_comment' => $data['OutSide_comment'], 'gate_status' => 'CheckedOut']);
                    Notification::make('success')->success()->title('Submitted Successfully')->send();
                })->visible(function ($record) {
                    if (auth()->user()->can('reception_visitor::request')) {
                        if ($record->OutSide_date !== null) {
                            return false;
                        }
                        if ($record->InSide_date !== null) {
                            return true;
                        }
                    }
                    return false;
                }),


                Tables\Actions\ViewAction::make()->infolist([
                    \Filament\Infolists\Components\Section::make([
                        TextEntry::make('employee.info')->label('Employee'),
                        IconEntry::make('ICON')->label('ICON')->boolean(),
                        RepeatableEntry::make('visitors_detail')->schema([
                            TextEntry::make('name'),
                            TextEntry::make('id')->label('ID/Passport'),
                            TextEntry::make('phone')->label('Phone'),
                            TextEntry::make('organization'),
                            TextEntry::make('type')->label('Type'),


                            TextEntry::make('remarks')->label('Remarks'),

                            TextEntry::make('attachment')->label('Attachments')->color('aColor')
                            ->url(fn($state)=>asset('images/'.$state))->openUrlInNewTab()


                                // ->hint(function ($state) {

                                //     return str('(' . public_path('images') . '/' . $state . ')')->inlineMarkdown()->toHtmlString();
                                // })->hintColor('primary')



                        ])->columns(6),
                        RepeatableEntry::make('driver_vehicle_detail')->schema([
                            TextEntry::make('name'),
                            TextEntry::make('id')->label('ID/Passport'),
                            TextEntry::make('phone')->label('Phone'),
                            TextEntry::make('model'),
                            TextEntry::make('color')->label('Color'),
                            TextEntry::make('Registration_Plate')->label('Registration Plate'),
                        ])->columns(6),

                    ]),
                    \Filament\Infolists\Components\Section::make([
                        TextEntry::make('InSide_date')->dateTime(),
                        TextEntry::make('inSide_comment'),
                    ])->columns(),
                    \Filament\Infolists\Components\Section::make([
                        TextEntry::make('OutSide_date')->dateTime(),
                        TextEntry::make('OutSide_comment'),
                    ])->columns(),
                ]),
                Tables\Actions\Action::make('pdf')->tooltip('Print')->icon('heroicon-s-printer')->iconSize(IconSize::Medium)->label('')
                    ->url(fn($record) => route('pdf.requestVisit', ['id' => $record->id]))->openUrlInNewTab(),

            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('print')->label('Print ')->iconSize(IconSize::Large)->icon('heroicon-s-printer')->color('primary')->action(function ($records) {
                    return redirect(route('pdf.requestVisits', ['ids' => implode('-', $records->pluck('id')->toArray()),'type'=>'Recurse']));

                }),
                ExportBulkAction::make()->color('purple')->label('Export Visitor Requests')->exports([
                    ExcelExport::make()->askForFilename('Visitor Form')->withColumns([
                        Column::make('employee.fullName'),
                        Column::make('visit_date'),
                        Column::make('arrival_time'),
                        Column::make('departure_time'),
                        Column::make('agency'),
                        Column::make('purpose'),
                        Column::make('visitors_detail')->formatStateUsing(function ($state) {
                            if (!is_array($state)) {
                                return '-';
                            }
                            $i = 0;
                            return collect($state['name'])->map(fn($item, $index) => ($i + 1) . ") " .
                                "Name: {$state['name']}, " .
                                "ID: {$state['id']}, " .
                                "Phone: {$state['phone']}, " .
                                "Organization: {$state['organization']}, " .
                                "Remarks: {$state['remarks']}")->implode("\n");
                        }),
                        Column::make('driver_vehicle_detail')->formatStateUsing(function ($state) {
                            if (!is_array($state)) {
                                return '-';
                            }
                            $i = 0;

                            return collect($state['name'])->map(fn($item, $index) => ($i + 1) . ") " .
                                "Name: {$state['name']}, " .
                                "ID: {$state['id']}, " .
                                "Phone: {$state['phone']}, " .
                                "Model: {$state['model']}, " .
                                "Color: {$state['color']}, " .
                                "Plate: {$state['Registration_Plate']}")->implode("\n");
                        }),
                        Column::make('approval_date'),
                        Column::make('status'),
                        Column::make('armed'),
                        Column::make('gate_status'),
                        Column::make('InSide_date'),
                        Column::make('OutSide_date'),
                        Column::make('inSide_comment'),
                        Column::make('OutSide_comment'),
                        Column::make('employee.fullName'),
                        Column::make('created_at'),
                    ])])->label('Export Visitor Requests')->color('purple') ,
            ]);
    }


    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getForm()
    {
        return [
            Section::make('Visitor Access Request')->schema([
                Section::make('')->schema([
                    Select::make('agency')->options(getCompany()->agency)->createOptionForm([
                        Forms\Components\TextInput::make('title')->required()
                    ])->createOptionUsing(function ($data) {
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

                    Forms\Components\TimePicker::make('arrival_time')->label('Arrival Time')->seconds(false)->before('departure_time')->required(),
                    Forms\Components\TimePicker::make('departure_time')->label('Departure Time')->seconds(false)->after('arrival_time')->required(),
                    Forms\Components\DatePicker::make('visit_date')->live()->label('Visit Date')->default(now()->addDay())->hintActions([
                        Forms\Components\Actions\Action::make('te')->label('Select Daily')->action(function (Forms\Get $get,Forms\Set $set){
                            $dates=$get('visiting_dates');
                            if ($get('visit_date')){
                                $dates[]= Carbon::createFromFormat('Y-m-d', $get('visit_date'))->format('d/m/Y') ;
                                $set('visiting_dates',$dates);
                            }
                            $set('visit_date',null);
                        }),Forms\Components\Actions\Action::make('Add')->label('Select Monthly')->form([
                            DateRangePicker::make('date')
                        ])->action(function (Forms\Get $get,Forms\Set $set,$data){
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
                    Select::make('visiting_dates')->required()->columnSpan(3)->label('Scheduled Visit Dates')->multiple(),
                    Forms\Components\TextInput::make('purpose')->columnSpanFull()->required(),
                ])->columns(4),
                Forms\Components\Repeater::make('visitors_detail')
                    ->addActionLabel('Add ')
                    ->label('Visitors Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')->label(' Name')->required(),
                        Forms\Components\TextInput::make('id')->label('ID/Passport')->required(),
                        Forms\Components\TextInput::make('phone')->label('Phone'),
                        Forms\Components\TextInput::make('organization')->label('Organization'),
                        Forms\Components\TextInput::make('remarks')->label('Remarks'),
                        FileUpload::make('attachment')->downloadable()
                            ->disk('public')->columnSpanFull(),
                    ])->columns(5)->columnSpanFull(),
                Section::make([
                    Forms\Components\Repeater::make('armed')->grid(3)->label('Armed Close Protection Officers (If Applicable)')->columnSpanFull()->schema([
                        Forms\Components\Select::make('type')->searchable()->disableOptionsWhenSelectedInSiblingRepeaterItems()->required()->columns(2)->label(' ')->options(['National' => 'National', 'International' => 'International', 'De-facto Security Forces' => 'De-facto Security Forces',]),
                        Forms\Components\TextInput::make('total')->numeric()->required()
                    ])->maxItems(3)->columns(2)->default(function () {
                        return [
                            ['type' => 'National', 'total' => 0],
                            ['type' => 'International', 'total' => 0],
                            ['type' => 'De-facto Security Forces', 'total' => 0],
                        ];
                    })->minItems(3)
                ]),
                Forms\Components\Repeater::make('driver_vehicle_detail')
                    ->addActionLabel('Add')
                    ->label('Drivers/Vehicles Detail')->schema([
                        Forms\Components\TextInput::make('name')->label('Full Name')->required(),
                        Forms\Components\TextInput::make('id')->label('ID/Passport')->required(),
                        Forms\Components\TextInput::make('phone')->label('Phone'),
                        Select::make('model')->options(getCompany()->visitrequest_model)->createOptionForm([
                            Forms\Components\TextInput::make('title')->required()
                        ])->createOptionUsing(function ($data) {
                            $array = getCompany()->visitrequest_model;
                            if (isset($array)) {
                                $array[$data['title']] = $data['title'];
                            } else {
                                $array = [$data['title'] => $data['title']];
                            }
                            getCompany()->update(['visitrequest_model' => $array]);
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
                                Forms\Components\TextInput::make('title')->required(),
                                Forms\Components\ColorPicker::make('color')->required()
                            ])
                            ->createOptionUsing(function ($data) {
                                $array = getCompany()->visitrequest_color ?? [];
                                $array[$data['title']] = $data['color'];
                                getCompany()->update(['visitrequest_color' => $array]);
                                return $data['title'];
                            })->allowHtml()
                            ->searchable()
                            ->preload()
                            ->label('Color'),
                        Forms\Components\TextInput::make('Registration_Plate')->required(),
                        Forms\Components\TextInput::make('trip')->required(),
                        FileUpload::make('driver')->label('Driver National Identification Card')->imageEditor()->image()->columnSpan(3),
                        FileUpload::make('image')->label('Vehicle Number Plate Photo')->imageEditor()->image()->columnSpan(4),

                    ])->columns(7)->columnSpanFull(),
                Forms\Components\Hidden::make('company_id')
                    ->default(getCompany()->id)
                    ->required(),

            ])->columns(2),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVisitorRequests::route('/'),
            'create' => Pages\CreateVisitorRequest::route('/create'),
//            'edit' => Pages\EditVisitorRequest::route('/{record}/edit'),
            'view' =>Pages\ViewVisitRequest::route('{record}/view')
        ];
    }
}
