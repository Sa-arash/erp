<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\VisitorRequestResource\Pages;
use App\Filament\Admin\Resources\VisitorRequestResource\RelationManagers;
use App\Models\VisitorRequest;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconSize;
use Filament\Tables;
use Filament\Tables\Table;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use function PHPUnit\Framework\isArray;

class VisitorRequestResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = VisitorRequest::class;
    protected static ?string $navigationLabel = 'Visit Access Request';
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
            'logo_and_name'
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Visitor Access Request')->schema([
                    Section::make('Visit’s Details')->schema([
                        Forms\Components\Select::make('requested_by')->live()
                            ->searchable()
                            ->preload()
                            ->required()
                            ->options(getCompany()->employees->pluck('fullName', 'id'))
                            ->default(fn() => auth()->user()->employee->id),
                            Select::make('agency')->options(getCompany()->agency)->createOptionForm([
                                Forms\Components\TextInput::make('title')->required()
                            ])->createOptionUsing(function ($data){
                                $array=getCompany()->agency;
                                if (isset($array)){
                                    $array[$data['title']]=$data['title'];

                                }else{
                                    $array=[$data['title']=>$data['title']];
                                }
                                getCompany()->update(['agency'=>$array]);
                                return $data['title'];
                            })->searchable()->preload(),

                        Forms\Components\DatePicker::make('visit_date')->default(now()->addDay())->required(),
                        Forms\Components\TimePicker::make('arrival_time')
                            ->seconds(false)
                            ->before('departure_time')
                            ->required(),
                        Forms\Components\TimePicker::make('departure_time')
                            ->seconds(false)
                            ->after('arrival_time')
                            ->required(),
                        Forms\Components\TextInput::make('purpose')->columnSpanFull()
                            ->required(),
                    ])->columns(5),
                    Forms\Components\Repeater::make('visitors_detail')
                        ->addActionLabel('Add')
                        ->label('Visitors Detail')
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->label('Full Name')
                                ->required(),
                            Forms\Components\TextInput::make('id')
                                ->label('ID/Passport')
                                ->required(),
                            Forms\Components\TextInput::make('phone')
                                ->label('Phone'),
                            Forms\Components\TextInput::make('organization')
                                ->label('Organization'),
                            Forms\Components\Select::make('type')
                                ->label('Type')
                                ->options([
                                    'National' => 'National',
                                    'International' => 'International',
                                    'De-facto Security Forces' => 'De-facto Security Forces',
                                ]),
                            Forms\Components\TextInput::make('remarks')
                                ->label('Remarks'),

                        ])->columns(6)->columnSpanFull(),
                    Forms\Components\Repeater::make('driver_vehicle_detail')
                        ->addActionLabel('Add')
                        ->label('Drivers/Vehicles Detail')->schema([
                            Forms\Components\TextInput::make('name')->label('Full Name')->required(),
                            Forms\Components\TextInput::make('id')->label('ID/Passport')->required(),
                            Forms\Components\TextInput::make('phone')->label('Phone'),
                            Forms\Components\TextInput::make('model')->required(),
                            Forms\Components\TextInput::make('color')->required(),
                            Forms\Components\TextInput::make('Registration_Plate')->required(),
                        ])->columns(6)->columnSpanFull(),
                    Forms\Components\Hidden::make('company_id')
                        ->default(getCompany()->id)
                        ->required(),
                ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('visit_date', 'desc')
            ->columns([

                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('employee.fullName')
                    ->label('Requester')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('visitors_detail')
                    ->label('Visitors')
                    ->state(fn($record) => implode(', ', (array_map(fn($item) => $item['name'], $record->visitors_detail))))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('visit_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('arrival_time')->time('H:m'),
                Tables\Columns\TextColumn::make('departure_time')->time('H:m'),
                Tables\Columns\TextColumn::make('status')->color(function ($state) {
                    switch ($state) {
                        case "approved":
                            return 'success';
                        case "Pending":
                            return 'info';
                        case "notApproved":
                            return 'danger';
                    }
                })->badge(),
                Tables\Columns\TextColumn::make('gate_status')->label('Gate Status')->badge(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('employee_id')->options(getCompany()->employees->pluck('info', 'id'))->searchable()->preload()->label('Employee'),
                DateRangeFilter::make('visit_date')->label('Visit Date'),
                Tables\Filters\SelectFilter::make('status')->options(['approved' => 'approved', 'notApproved' => 'notApproved'])->searchable()
            ], getModelFilter())
            ->actions([
                Tables\Actions\Action::make('ActionInSide')->label('CheckIn')->form([
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
                Tables\Actions\Action::make('ActionOutSide')->label('CheckOut')->form([
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
                        RepeatableEntry::make('visitors_detail')->schema([
                            TextEntry::make('name'),
                            TextEntry::make('id')->label('ID/Passport'),
                            TextEntry::make('phone')->label('Phone'),
                            TextEntry::make('organization'),
                            TextEntry::make('type')->label('Type'),
                            TextEntry::make('remarks')->label('Remarks'),
                        ])->columns(5),
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
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
                Section::make('Visit’s Details')->schema([
                    Forms\Components\DatePicker::make('visit_date')->default(now()->addDay())->required(),
                    Forms\Components\TimePicker::make('arrival_time')->seconds(false)->before('departure_time')->required(),
                    Forms\Components\TimePicker::make('departure_time')->seconds(false)->after('arrival_time')->required(),
                    Forms\Components\TextInput::make('purpose')->columnSpanFull()
                        ->required(),
                ])->columns(4),
                Forms\Components\Repeater::make('visitors_detail')
                    ->addActionLabel('Add')
                    ->label('Visitors Detail')
                    ->schema([
                        Forms\Components\TextInput::make('name')->label('Full Name')->required(),
                        Forms\Components\TextInput::make('id')->label('ID/Passport')->required(),
                        Forms\Components\TextInput::make('phone')->label('Phone'),
                        Forms\Components\TextInput::make('organization')->label('Organization'),
                        Forms\Components\Select::make('type')->searchable()->label('Type')->options(['National' => 'National', 'International' => 'International', 'De-facto Security Forces' => 'De-facto Security Forces',]),
                        Forms\Components\TextInput::make('remarks')->label('Remarks'),
                    ])->columns(6)->columnSpanFull(),
                Forms\Components\Repeater::make('driver_vehicle_detail')
                    ->addActionLabel('Add')
                    ->label('Drivers/Vehicles Detail')->schema([
                        Forms\Components\TextInput::make('name')->label('Full Name')->required(),
                        Forms\Components\TextInput::make('id')->label('ID/Passport')->required(),
                        Forms\Components\TextInput::make('phone')->label('Phone'),
                        Forms\Components\TextInput::make('model')->label('Vehicles Model')->required(),

                        Forms\Components\TextInput::make('color')->label('Vehicles Color')->required(),
                        Forms\Components\TextInput::make('Registration_Plate')->required(),
                    ])->columns(6)->columnSpanFull(),
            ])->columns(2)
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVisitorRequests::route('/'),
            'create' => Pages\CreateVisitorRequest::route('/create'),
            'edit' => Pages\EditVisitorRequest::route('/{record}/edit'),
        ];
    }
}
