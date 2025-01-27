<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AssetEmployeeResource\Pages;
use App\Filament\Admin\Resources\AssetEmployeeResource\RelationManagers;
use App\Models\Asset;
use App\Models\AssetEmployee;
use App\Models\AssetEmployeeItem;
use App\Models\Structure;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconSize;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AssetEmployeeResource extends Resource
{
    protected static ?string $model = AssetEmployee::class;
    protected static ?string $navigationGroup = 'Stock Management';
    protected static ?string $label="Check in/Check out Assets";

    protected static ?string $navigationIcon = 'heroicon-s-arrows-up-down';

    public static function form(Form $form): Form
    {

        return $form
            ->schema([
                Forms\Components\Select::make('employee_id')->label('Employee')->options(function () {
                    $data = [];
                    $employees = getCompany()->employees;
                    foreach ($employees as $employee) {
                        $data[$employee->id] = $employee->fullName . " (ID # " . $employee->ID_number . " )";
                    }
                    return $data;
                })->searchable()->required(),
                Forms\Components\DateTimePicker::make('date')->label('Distribution Date')->withoutTime()->default(now())->required(),
                Forms\Components\Textarea::make('description')->columnSpanFull(),
                Forms\Components\Repeater::make('AssetEmployeeItem')->relationship('assetEmployeeItem')->schema([
                    Forms\Components\Select::make('asset_id')
                        ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                        ->live()->label('Asset')->options(function () {
                            $data = [];
                            $assets = Asset::query()->where('status', 'inStorageUsable')->with('product')->where('company_id', getCompany()->id)->get();
                            foreach ($assets as $asset) {
                                $data[$asset->id] = $asset->product?->title . " ( SKU #" . $asset->product?->sku  . " )";
                            }
                            return $data;
                        })->required()->searchable()->preload(),
                    Forms\Components\Select::make('warehouse_id')->live()->label('Warehouse/Building')->options(getCompany()->warehouses()->pluck('title', 'id'))->required()->searchable()->preload(),
                    SelectTree::make('structure_id')->label('Location')->defaultOpenLevel(2)->model(Structure::class)->relationship('parent', 'title', 'parent_id', modifyQueryUsing: function ($query, Forms\Get $get) {
                        return $query->where('warehouse_id', $get('warehouse_id'));
                    })->required(),
                    Forms\Components\DatePicker::make('due_date'),
                ])->columnSpanFull()->columns(4)->mutateRelationshipDataBeforeCreateUsing(function ($data) {
                    $data['company_id'] = getCompany()->id;
                    return $data;
                })
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('id','desc')
            ->columns([

                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('employee.fullName')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('date')->date()->sortable(),
                Tables\Columns\TextColumn::make('description')->sortable(),
                Tables\Columns\TextColumn::make('type')->color(fn($state)=>$state==="Returned" ?  "danger":"success")->sortable()->badge(),
                Tables\Columns\TextColumn::make('status')->sortable()->badge(),

            ])
            ->filters([
                //
            ])
            ->actions([
//                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make()->modalHeading(fn($record)=>$record->type==="Returned" ? "Check In " :"Check Out"),
                Tables\Actions\Action::make('approve')->form([
                    Forms\Components\Textarea::make('note')
                ])->iconSize(IconSize::Medium)->color('success')->icon( 'heroicon-m-cog-8-tooth' )->label('Approve Returned' )->requiresConfirmation()->action(function ($record,$data) {
                    $record->update([
                        'status' => "Approve",
                        'note'=>$data['note']
                    ]);
                    foreach ($record->assetEmployeeItem as $item) {

                        AssetEmployeeItem::query()->where('asset_id',$item->asset_id)->update([
                            'type'=>1,
                        ]);
                        $item->update([
                            'return_approval_date'=>now()
                        ]);
                        Asset::query()->where('id',$item->asset_id)->update([
                            'status'=>'inStorageUsable',
                            'warehouse_id'=>$item->warehouse_id,
                            'structure_id'=>$item->structure_id,

                        ]);
                    }
                    Notification::make('success')->success()->title('Approved')->send();
                })->hidden(fn($record)=>$record->status==="Approve")
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
    public static function getNavigationBadge(): ?string
    {
        return self::$model::query()->where('status', 'Pending')->where('company_id', getCompany()->id)->count();
    }
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema(function ($record){

            if ($record->type==="Returned"){
                return [
                    TextEntry::make('employee.fullName')->color('aColor')->badge()->url(fn($record)=>EmployeeResource::getUrl('view',['record'=>$record->employee_id])),
                    TextEntry::make('date')->label('Return Date')->date(),
                    TextEntry::make('approve_date')->label('Approve Return Date')->date(),
                    TextEntry::make('description'),
                    RepeatableEntry::make('assetEmployeeItem')->label('Returned Assets')->schema([
                        TextEntry::make('asset.product.title'),
                        TextEntry::make('warehouse.title')->label('Location'),
                        TextEntry::make('structure.title')->label('Address'),
                    ])->columns(3)->columnSpanFull()

                ];
            }else{
                return [
                    TextEntry::make('employee.fullName')->color('aColor')->badge()->url(fn($record)=>EmployeeResource::getUrl('view',['record'=>$record->employee_id])),
                    TextEntry::make('date')->date(),
                    TextEntry::make('description'),
                    RepeatableEntry::make('assetEmployeeItem')->schema([
                        TextEntry::make('asset.product.title'),
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
            'create' => Pages\CreateAssetEmployee::route('/create'),
            'edit' => Pages\EditAssetEmployee::route('/{record}/edit'),
        ];
    }
}
