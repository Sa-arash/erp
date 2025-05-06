<?php

namespace App\Filament\Admin\Widgets;

use App\Filament\Admin\Resources\VisitorRequestResource;
use App\Filament\Admin\Resources\VisitorRequestResource\Pages\EditVisitorRequest;
use App\Models\VisitorRequest;
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

class VisitRequest extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                VisitorRequest::query()->where('company_id', getCompany()->id)->where('requested_by',getEmployee()->id)
                // ->where('status', '!=', 'FinishedCeo')
            )
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),

                Tables\Columns\TextColumn::make('visitors_detail')
                    ->label('Visitors')
                    ->state(fn($record) => implode(', ', (array_map(fn($item) => $item['name'], $record->visitors_detail))))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('visit_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('arrival_time')->time('H:m'),
                Tables\Columns\TextColumn::make('departure_time')->time('H:m'),

                Tables\Columns\TextColumn::make('status')->badge()->color(function ($state) {
                    switch ($state) {
                        case "approved":
                            return 'success';
                        case "Pending":
                            return 'info';
                        case "notApproved":
                            return 'danger';
                    }
                }),

                Tables\Columns\TextColumn::make('employee.fullName')
                ->label('Requester')
                ->numeric()
                ->toggleable(isToggledHiddenByDefault: true)
                ->sortable(),
            ])

            ->actions([
                EditAction::make()->form(VisitorRequestResource::getForm())->modalWidth(MaxWidth::Full),
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
                                DatePicker::make('visit_date')->label('Visit Date')->default(now()->addDay())->required(),
                                TimePicker::make('arrival_time')->label('Arrival Time')->seconds(false)->before('departure_time')->required(),
                                TimePicker::make('departure_time')->label('Departure Time')->seconds(false)->after('arrival_time')->required(),
                                TextInput::make('purpose')->columnSpanFull()->required(),
                            ])->columns(4),
                            Repeater::make('visitors_detail')->addActionLabel('Add')->label('Visitors Detail')->schema([
                                TextInput::make('name')->label('Name')->required(),
                                TextInput::make('id')->label('ID/Passport')->required(),
                                TextInput::make('phone')->label('Phone'),
                                TextInput::make('organization')->label('Organization'),
                                Textarea::make('remarks')->columnSpan(3)->label('Remarks'),
                                ToggleButtons::make('type')->required()->grouped()->columnSpan(2)->label('Type')->options(['National' => 'National', 'International' => 'International', 'De-facto Security Forces' => 'De-facto Security Forces',]),


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
                                    Select::make('color')->options(getCompany()->visitrequest_color)->createOptionForm([
                                        TextInput::make('title')->required()
                                    ])->createOptionUsing(function ($data){
                                        $array=getCompany()->visitrequest_color;
                                        if (isset($array)){
                                            $array[$data['title']]=$data['title'];
        
                                        }else{
                                            $array=[$data['title']=>$data['title']];
                                        }
                                        getCompany()->update(['visitrequest_color'=>$array]);
                                        return $data['title'];
                                    })->searchable()->preload(),
                                    TextInput::make('Registration_Plate')->required(),
                                ])->columns(3)->columnSpanFull(),
                            FileUpload::make('attachment')->columnSpanFull()->label('File Upload')
                        ])->columns(2)
                    ]
                )->action(function (array $data): void {

                   $visitorRequest = VisitorRequest::query()->create([
                        'visit_date'=>$data['visit_date'],
                        'arrival_time'=>$data['arrival_time'],
                        'departure_time'=>$data['departure_time'],
                        'purpose'=>$data['purpose'],
                        'visitors_detail'=>$data['visitors_detail'],
                        'driver_vehicle_detail'=>$data['driver_vehicle_detail'],
                        'requested_by'=>getEmployee()->id,
                        'company_id'=>getCompany()->id,
                    ]);
                    if ($data['attachment']){
                        $visitorRequest->addMedia(public_path('images/'.$data['attachment']))->toMediaCollection('attachment');
                    }
                    sendAR(getEmployee(),$visitorRequest,getCompany());
                    // sendSecurity(getEmployee(),$visitorRequest,getCompany());
                    Notification::make('success')->color('success')->success()->title('Request Sent')->send()->sendToDatabase(auth()->user());

                })
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
