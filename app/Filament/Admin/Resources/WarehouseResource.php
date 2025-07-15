<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\WarehouseResource\Pages;
use App\Filament\Admin\Resources\WarehouseResource\RelationManagers;
use App\Models\Structure;
use App\Models\Warehouse;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class WarehouseResource extends Resource
    implements HasShieldPermissions

{
    protected static ?string $model = Warehouse::class;
    protected static ?string $navigationGroup = 'Logistic Management';
    protected static ?string $navigationIcon = 'heroicon-c-home-modern';
    protected static ?int $navigationSort=4;
    protected static ?string $label="Warehouse";
    protected static ?string $pluralLabel="Warehouse";


    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'fullManager'
        ];
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')->label('Warehouse Name')->required()->maxLength(255),
                Select::make('employee_id')->required()->label('Manage By')->searchable()->preload()->options(getCompany()->employees()->get()->pluck('fullName', 'id'))->disabled(fn()=>!\auth()->user()->can('fullManager_warehouse')),
                Forms\Components\TextInput::make('phone')->tel()->maxLength(255),
                Forms\Components\Select::make('country')->options(getCountry())->searchable()->preload(),
                Forms\Components\TextInput::make('state')->label('State/Province')->maxLength(255),
                Forms\Components\TextInput::make('city')->maxLength(255),
                Forms\Components\Textarea::make('address')->maxLength(255)->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->headerActions([
            ExportAction::make()
            ->after(function (){
                if (Auth::check()) {
                    activity()
                        ->causedBy(Auth::user())
                        ->withProperties([
                            'action' => 'export',
                        ])
                        ->log('Export' . "Warehouse");
                }
            })->exports([
                ExcelExport::make()->askForFilename("Warehouse")->withColumns([
                    Column::make('title')->heading('Location Name'),
                    Column::make('employee.fullName')->heading('Manager By'),
                    Column::make('phone'),
                    Column::make('country'),
                    Column::make('state'),
                    Column::make('city'),
                    Column::make('address'),
                ]),
            ])->label('Export Warehouse')->color('purple')
        ])
        ->query(function (){
            if (\auth()->user()->can('fullManager_warehouse')){
                return  Warehouse::query()->where('type',1)->where('company_id',getCompany()->id);
            }else{
                return Warehouse::query()->where('type',1)->where('company_id',getCompany()->id)->where('employee_id',getEmployee()->id);
            }
        })
            ->columns([
                Tables\Columns\TextColumn::make(getRowIndexName())->rowIndex(),
                Tables\Columns\TextColumn::make('title')->label('Location Name')->searchable(),
                Tables\Columns\TextColumn::make('employee.fullName')->label('Manager By')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('phone')->searchable(),
                Tables\Columns\TextColumn::make('country')->searchable(),
                Tables\Columns\TextColumn::make('state')->searchable(),
                Tables\Columns\TextColumn::make('city')->searchable(),
                Tables\Columns\TextColumn::make('address')->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->modelLabel('Edit'),
                Tables\Actions\Action::make('add')->label('Add Structure')->form(function ($record){
                    return [
                        Forms\Components\TextInput::make('title')->required()->maxLength(255),
                        Forms\Components\ToggleButtons::make('location')->live()->grouped()->required()->default(0)->boolean('Building','Warehouse'),
                        SelectTree::make('parent_id')->label('Parent')->enableBranchNode()->defaultOpenLevel(2)->model(Structure::class)->relationship('parent', 'title', 'parent_id',modifyQueryUsing: function($query,Get $get)use($record){
                            return $query->where('warehouse_id', $record->id)->where('location',$get('location'));
                        }),
                        Select::make('type')->label('Type')->live()->options(getCompany()->warehouse_type)->searchable()->preload()->required()->createOptionForm([
                            TextInput::make('title')->required()->maxLength(50)
                        ])->createOptionUsing(function ($data) {
                            $array = getCompany()->warehouse_type;
                            if (isset($array)) {
                                $array[$data['title']] = $data['title'];
                            } else {
                                $array = [$data['title'] => $data['title']];
                            }
                            getCompany()->update(['warehouse_type' => $array]);
                            return $data['title'];
                        })->fillEditOptionActionFormUsing(function ($state) {
                            return [
                                'title' => $state
                            ];
                        })->editOptionForm([
                            TextInput::make('title')->required()->maxLength(50)
                        ])->updateOptionUsing(function ($data, $state,Forms\Set $set) {
                            $oldValue = $state;
                            $company = getCompany();
                            $types = $company->warehouse_type ?? [];
                            Structure::query()->where('type', $oldValue)->update(['type' => $data['title']]);
                            unset($types[$oldValue]);
                            $types[$data['title']] = $data['title'];
                            $company->update(['warehouse_type' => $types]);
                            sendSuccessNotification();
                            $set('type',$data['title']);
                            return $data['title'];
                        })
                    ];
                })->action(function ($data,$record) {
                    Structure::query()->create([
                            'title'=>$data['title'],
                            'parent_id'=>$data['parent_id'],
                            'warehouse_id'=>$record->id,
                            'type'=>$data['type'],
                            'location'=>$data['location'],
                            'company_id'=>getCompany()->id,
                        ]);
                    Notification::make('save')->success()->title('Save ')->send();
                })->icon('heroicon-s-home-modern')->color('warning'),
                Tables\Actions\Action::make('inventory')->icon('heroicon-s-inbox-arrow-down')->color('success')->url(fn($record)=>WarehouseResource::getUrl('inventory',['record'=>$record->id])),
                Tables\Actions\DeleteAction::make()->hidden(fn($record)=>$record->employees->count() or $record->assets->count() or $record->inventories->count())->visible(fn()=>\auth()->user()->can('fullManager_warehouse'))

            ])
            ->bulkActions([
                ExportBulkAction::make()
                ->after(function (){
                    if (Auth::check()) {
                        activity()
                            ->causedBy(Auth::user())
                            ->withProperties([
                                'action' => 'export',
                            ])
                            ->log('Export' . "Warehouse");
                    }
                })->exports([
                    ExcelExport::make()->askForFilename("Warehouse")->withColumns([
                        Column::make('title')->heading('Location Name'),
                        Column::make('employee.fullName')->heading('Manager By'),
                        Column::make('phone'),
                        Column::make('country'),
                        Column::make('state'),
                        Column::make('city'),
                        Column::make('address'),
                    ]),
                ])->label('Export Warehouse')->color('purple')
            ]);
    }

    public static function getRelations(): array
    {
        return [
                RelationManagers\StructuresRelationManager::class,
                RelationManagers\ProductsRelationManager::class,
        ];
    }
    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            Pages\Inventory::class,
            Pages\InventoryStock::class,

        ]);
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWarehouses::route('/'),
            'create' => Pages\CreateWarehouse::route('/create'),
            'edit' => Pages\EditWarehouse::route('/{record}/edit'),
            'inventory'=>Pages\Inventory::route('/{record}/inventory'),
            'stock'=>Pages\InventoryStock::route('/{record}/stock')
        ];
    }
}
