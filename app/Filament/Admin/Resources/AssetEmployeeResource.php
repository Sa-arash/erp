<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AssetEmployeeResource\Pages;
use App\Filament\Admin\Resources\AssetEmployeeResource\RelationManagers;
use App\Models\Asset;
use App\Models\AssetEmployee;
use App\Models\AssetEmployeeItem;
use App\Models\Person;
use App\Models\Structure;
use Carbon\Carbon;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconSize;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class AssetEmployeeResource extends Resource
{
    protected static ?string $model = AssetEmployee::class;
    protected static ?string $navigationGroup = 'Logistic Management';
    protected static ?int $navigationSort = 9;

    protected static ?string $label = "Assets IN / OUT ";

    protected static ?string $navigationIcon = 'heroicon-s-arrows-up-down';

    public static function form(Form $form): Form
    {

        return $form
            ->schema([
                Forms\Components\Section::make([
                    Forms\Components\Select::make('employee_id')->label('Employee')->options(function () {
                        $data = [];
                        $employees = getCompany()->employees;
                        foreach ($employees as $employee) {
                            $data[$employee->id] = $employee->fullName . " (ID # " . $employee->ID_number . " )";
                        }
                        return $data;
                    })->searchable()->requiredWithout('person')->prohibits('person'),
                    Select::make('person_id')->label('Person')->options(function (){
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
                    Forms\Components\DateTimePicker::make('date')->label('Distribution Date')->withoutTime()->default(now())->required(),
                ])->columns(3),
                Forms\Components\Textarea::make('description')->columnSpanFull(),
                Forms\Components\Repeater::make('AssetEmployeeItem')->required()->relationship('assetEmployeeItem')->schema([
                    Forms\Components\Select::make('asset_id')
                        ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                        ->live()->label('Asset')->options(function () {
                            $data = [];
                            $assets = Asset::query()->where('status', 'inStorageUsable')->with('product')->where('company_id', getCompany()->id)->get();
                            foreach ($assets as $asset) {
                                $data[$asset->id] = $asset->title;
                            }
                            return $data;
                        })->required()->searchable()->preload(),
                    Forms\Components\Select::make('warehouse_id')->live()->label('Warehouse/Building')->options(getCompany()->warehouses()->pluck('title', 'id'))->searchable()->preload(),
                    SelectTree::make('structure_id')->label('Location')->defaultOpenLevel(2)->model(Structure::class)->relationship('parent', 'title', 'parent_id', modifyQueryUsing: function ($query, Forms\Get $get) {
                        return $query->where('warehouse_id', $get('warehouse_id'));
                    }),
                    Forms\Components\DatePicker::make('due_date'),
                ])->columnSpanFull()->columns(4)->mutateRelationshipDataBeforeCreateUsing(function ($data) {
                    $data['company_id'] = getCompany()->id;
                    return $data;
                })->default(function () {
                    if (request('asset')) {
                        $asset = Asset::query()->find((int) request('asset'));
                        if ($asset) {
                            return [
                                [
                                    'asset_id' => $asset->id,
                                    'warehouse_id' => null,
                                    'structure_id' => null,
                                    'due_date' => null,
                                ],
                            ];
                        }
                    }
                    return [];
                })->addActionLabel('Add Asset')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')->headerActions([
                ExportAction::make()
                    ->after(function () {
                        if (Auth::check()) {
                            activity()
                                ->causedBy(Auth::user())
                                ->withProperties([
                                    'action' => 'export',
                                ])
                                ->log('Export' . "Check IN/Check OUT Assets");
                        }
                    })->exports([
                        ExcelExport::make()->askForFilename("Check IN&Check OUT Assets")->withColumns([
                            Column::make('id')->formatStateUsing(fn($record)=>$record->employee_id ? $record->employee->fullName : $record->person )->heading('Employee/Person'),
                            Column::make('date'),
                            Column::make('description'),
                        ]),
                    ])->label('Export Report')->color('purple')
            ])
            ->columns([

                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('employee.fullName')->state(fn($record) => $record->employee_id ? $record->employee->fullName : $record->person?->name . '(' . $record->person?->number . ')')->label('Employee/Personnel')->sortable(),
                Tables\Columns\TextColumn::make('date')->date()->sortable(),
                Tables\Columns\TextColumn::make('description')->sortable(),
                Tables\Columns\TextColumn::make('assetEmployeeItem.asset.product.title')->state(function ($record){
                    $sub = AssetEmployeeItem::selectRaw('MAX(id) as id')
                        ->whereHas('assetEmployee', function ($q)use($record) {
                            if ($record->employee_id){
                                $q->where('employee_id', $record->employee_id);
                            }else{
                                $q->where('person_id', $record->person_id);
                            }
                        })
                        ->groupBy('asset_id');

                    return AssetEmployeeItem::query()
                        ->whereIn('id', $sub)
                        ->where('type', 'Assigned')->get()->map(function ($item){
                            if ($item->asset->description){
                                return $item->asset->product->title.'('.$item->asset->description.')';
                            }else{
                                return $item->asset->product->title;
                            }
                        });
                })->label('Assets')->bulleted(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('employee_id')->label('Employee')->searchable()->options(getCompany()->employees()->pluck('fullName','id'))->preload(),
                Tables\Filters\SelectFilter::make('person_id')->label('Personnel')->searchable()->options(function (){
                    $data=[];
                    $persons=Person::query()->where('company_id',getCompany()->id)->get();
                    foreach ($persons as $person){
                        if ($person->number){
                            $data[$person->id]=$person->name.'('.$person->number.')';
                        }else{
                            $data[$person->id]=$person->name;
                        }
                    }
                    return $data;
                })->preload(),
                DateRangeFilter::make('date'),
            ],getModelFilter())
            ->actions([
                Tables\Actions\Action::make('pdf')->color('warning')->tooltip('Print History')->icon('heroicon-s-printer')->iconSize(IconSize::Medium)->label('Print History')->url(fn($record) => route('pdf.employeeAssetHistory', ['id' => $record->id,'type'=>'ID','company'=>$record->company_id]))->openUrlInNewTab(),
                Tables\Actions\Action::make('pdf')->color('success')->tooltip('Print Assets')->icon('heroicon-s-printer')->iconSize(IconSize::Medium)->label('Print Assets')->url(fn($record) => route('pdf.employeeAsset', ['id' => $record->id,'type'=>'ID','company'=>$record->company_id]))->openUrlInNewTab(),

            ])
            ->bulkActions([

                ExportBulkAction::make()
                    ->after(function () {
                        if (Auth::check()) {
                            activity()
                                ->causedBy(Auth::user())
                                ->withProperties([
                                    'action' => 'export',
                                ])
                                ->log('Export' . "Check IN/Check OUT Assets");
                        }
                    })->exports([
                        ExcelExport::make()->askForFilename("Check IN&Check OUT Assets")->withColumns([
                            Column::make('id')->formatStateUsing(fn($record)=>$record->employee_id ? $record->employee->fullName : $record->person )->heading('Employee/Person'),
                            Column::make('date'),
                            Column::make('description'),
                            Column::make('type'),
                            Column::make('status'),
                        ]),
                    ])->label('Export Report')->color('purple')

            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema(function ($record) {

            if ($record->type === "Returned") {
                return [
                    TextEntry::make('employee')->label('Employee/Person')->color('aColor')->badge()->state(fn($record)=>$record->employee_id ? $record->employee->fullName : $record->person?->name.'('.$record->person->number.')' ),
                    TextEntry::make('date')->label('Return Date')->date(),
                    TextEntry::make('approve_date')->label('Approve Return Date')->date(),
                    TextEntry::make('description'),
                    RepeatableEntry::make('assetEmployeeItem')->label('Returned Assets')->schema([
                        TextEntry::make('asset.titlen'),
                        TextEntry::make('warehouse.title')->label('Location'),
                        TextEntry::make('structure.title')->label('Address'),
                    ])->columns(3)->columnSpanFull()

                ];
            } else {
                return [
                    TextEntry::make('employee.fullName')->label('Employee/Person')->state(fn($record)=>$record->employee_id ? $record->employee->fullName : $record->person?->name.'('.$record->person?->number.')' )->color('aColor')->badge() ,
                    TextEntry::make('date')->date(),
                    TextEntry::make('description'),
                    RepeatableEntry::make('assetEmployeeItem')->schema([
                        TextEntry::make('asset.titlen'),
                        TextEntry::make('warehouse.title')->label('Location'),
                        TextEntry::make('structure.title')->label('Address'),
                        TextEntry::make('due_date')->date(),
                    ])->columns(4)->columnSpanFull()

                ];
            }
        });
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssetEmployees::route('/'),
//            'create' => Pages\CreateAssetEmployee::route('/create'),
//            'edit' => Pages\EditAssetEmployee::route('/{record}/edit'),
        ];
    }
}
