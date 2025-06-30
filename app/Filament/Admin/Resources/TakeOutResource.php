<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TakeOutResource\Pages;
use App\Filament\Admin\Resources\TakeOutResource\RelationManagers;
use App\Models\AssetEmployeeItem;
use App\Models\Employee;
use App\Models\TakeOut;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconSize;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class TakeOutResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = TakeOut::class;

    protected static ?string $navigationIcon = 'heroicon-c-arrow-up-tray';
    protected static ?string $navigationGroup = 'Security Management';
    protected static ?int $navigationSort = 99;

    protected static ?string $label = 'Gate Pass';
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
            'security'
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')->headerActions([
                ExportAction::make()->after(function (){
                    if (Auth::check()) {
                        activity()
                            ->causedBy(Auth::user())
                            ->withProperties([
                                'action' => 'export',
                            ])
                            ->log('Export' . 'Gate Pass');
                    }
                })->exports([
                    ExcelExport::make()->askForFilename("Gate Pass")->withColumns([
                        Column::make('employee.fullName')->heading("Employee"),
                        Column::make('assets.product.title')->formatStateUsing(fn($record) => $record->assets->pluck('title')->toArray())->heading('Registered Asset'),
                        Column::make('itemsOut')->heading('Unregistered Asset')->formatStateUsing(function ($record) {
                            $data = [];
                            if ($record->itemsOut) {
                                foreach ($record->itemsOut as $item) {
                                    $data[] = $item['name'];
                                }
                            }
                            return $data;
                        }),
                        Column::make('from'),
                        Column::make('to'),
                        Column::make('date'),
                        Column::make('return_date'),
                        Column::make('mood'),
                        Column::make('status'),
                        Column::make('type'),
                        Column::make('gate_status')->heading('Gate Status'),
                    ]),
                ])->label('Export Gate Pass')->color('purple')
            ])
            ->columns([
                Tables\Columns\TextColumn::make('NO')->label('No')->rowIndex(),
                Tables\Columns\TextColumn::make('employee.fullName')->label('Employee Name'),
                Tables\Columns\TextColumn::make('assets.product.title')->state(fn($record) => $record->assets->pluck('title')->toArray())->badge()->label('Registered Asset'),
                Tables\Columns\TextColumn::make('itemsOut')->label('Unregistered Asset')->state(function ($record) {
                    $data = [];
                    if ($record->itemsOut) {
                        foreach ($record->itemsOut as $item) {
                            $data[] = $item['name'];
                        }
                    }
                    return $data;
                })->limitList(5)->badge(),
                Tables\Columns\TextColumn::make('from'),
                Tables\Columns\TextColumn::make('to'),
                Tables\Columns\TextColumn::make('date')->date(),
                Tables\Columns\TextColumn::make('return_date')->date(),
                Tables\Columns\TextColumn::make('mood')->label('Status')->color(function ($state) {
                    switch ($state) {
                        case "Approved":
                            return 'success';
                        case "Pending":
                            return 'info';
                        case "NotApproved":
                            return 'danger';
                    }
                })->badge(),
                Tables\Columns\TextColumn::make('approvals.comment')->label('Comment')->wrap(),
                Tables\Columns\TextColumn::make('status')->label('Status Items')->badge(),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('gate_status')->label('Gate Status')->badge(),
                Tables\Columns\TextColumn::make('OutSide_date')->label('Take OUT ')->dateTime(),
                Tables\Columns\TextColumn::make('InSide_date')->label('Take IN ')->dateTime(),

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('employee_id')->searchable()->label('Employee')->options(Employee::query()->where('company_id', getCompany()->id)->pluck('fullName', 'id')),
                DateRangeFilter::make('date')->label('Date'),
                Tables\Filters\SelectFilter::make('status')->options(['Returnable'=>'Returnable','Non-Returnable'=>'Non-Returnable'])->searchable()->preload(),
                Tables\Filters\SelectFilter::make('gate_status')->options(['Pending'=>'Pending','CheckedIn'=>'Checked IN','CheckedOut'=>'Checked OUT','Canceled'=>'Canceled'])->searchable()->preload(),
            ], getModelFilter())
            ->actions([
                Tables\Actions\Action::make('ActionOutSide')->label('Take OUT ')->form([
                    Forms\Components\DateTimePicker::make('OutSide_date')->withoutSeconds()->label(' Date And Time')->required()->default(now()),
                    Forms\Components\Textarea::make('OutSide_comment')->label(' Comment')
                ])->requiresConfirmation()->action(function ($data, $record) {
                    foreach($record->items as $item){
                        $latestAssetEmployee = AssetEmployeeItem::query()->orderBy('id','desc')->firstWhere('asset_id',$item->asset_id);
                        if ($latestAssetEmployee) {
                            $newAssetEmployee = $latestAssetEmployee->replicate();
                            $newAssetEmployee->type = 'Gate Pass';
                            $newAssetEmployee->due_date = $record->return_date;
                            $newAssetEmployee->warehouse_id = null;
                            $newAssetEmployee->structure_id = null;
                            $newAssetEmployee->description = $item->remarks;
                            $newAssetEmployee->save();
                        }
                    }

                    $record->update(['OutSide_date' => $data['OutSide_date'], 'OutSide_comment' => $data['OutSide_comment'], 'gate_status' => 'CheckedOut']);
                    Notification::make('success')->success()->title('Submitted Successfully')->send();
                })->visible(function ($record) {
                    if (auth()->user()->can('reception_take::out')) {
                        if ($record->mood === "Approved") {
                            if ($record->OutSide_date === null) {
                                return true;
                            }
                        }
                    }
                    return false;
                }),
                Tables\Actions\Action::make('ActionInSide')->label('Take IN ')->form([
                    Forms\Components\DateTimePicker::make('InSide_date')->withoutSeconds()->label(' Date And Time')->required()->default(now()),
                    Forms\Components\Textarea::make('inSide_comment')->label(' Comment')
                ])->requiresConfirmation()->action(function ($data, $record) {
                    foreach($record->items as $item){

//                        $latestAssetEmployee = $item->asset->assetEmployee->sortByDesc('date')->first();
//
//                        if ($latestAssetEmployee) {
//
//                            $newAssetEmployee = $latestAssetEmployee->replicate();
//
//                            $newAssetEmployee->type = 'Assigned';
//
//                            $newAssetEmployee->save();
//                        }
                    }
                    $record->update(['InSide_date' => $data['InSide_date'], 'inSide_comment' => $data['inSide_comment'], 'gate_status' => 'CheckedIn']);
                    Notification::make('success')->success()->title('Submitted Successfully')->send();
                })->visible(function ($record) {
                    if ($record->status !== "Non-Returnable") {
                        if (auth()->user()->can('reception_take::out')) {
                            if ($record->InSide_date !== null) {
                                return false;
                            }
                            if ($record->OutSide_date !== null) {
                                return true;
                            }
                        }
                    }
                    return  false;
                }),
                Tables\Actions\ViewAction::make('view')->slideOver()->infolist([
                    Section::make([
                        TextEntry::make('employee.fullName'),
                        ImageEntry::make('pic')->state(function ($record) {
                            return $record->employee->media->where('collection_name', 'images')->first()?->original_url;
                        })
                            ->defaultImageUrl(fn($record) => $record->employee->gender === "male" ? asset('img/user.png') : asset('img/female.png'))
                            ->label('Employee Photo')
                            ->width(80)
                            ->height(80),
                        TextEntry::make('from'),
                        TextEntry::make('to'),
                        TextEntry::make('date')->date(),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('type')->badge(),
                        ImageEntry::make('media.original_url')->label('Attach Document & Supporting Document'  )->height(100),
                        RepeatableEntry::make('items')->label('Assets')->schema([
                            TextEntry::make('asset.description')->label('Asset Description'),
                            TextEntry::make('asset.number')->label('Asset Number'),
                            TextEntry::make('remarks'),
                            TextEntry::make('returned_date'),
                        ])->columnSpanFull()->columns(4),
                        RepeatableEntry::make('itemsOut')->label('Unregistered Asset')->schema([
                            TextEntry::make('name'),
                            TextEntry::make('quantity'),
                            TextEntry::make('unit'),
                            TextEntry::make('remarks'),
                            TextEntry::make('status')->color(fn ($state)=>match ($state){
                                'Approved'=>'success','Not Approved'=>'danger','Pending'=>'primary'
                            })->badge(),
                            ImageEntry::make('image')->width(100)->height(100)
                                ->url(fn($state)=>asset('images/'.$state))
                            ,
                        ])->columnSpanFull()->columns(6),
                        \Filament\Infolists\Components\Section::make([
                            TextEntry::make('OutSide_date')->label('Outside Date')->dateTime(),
                            TextEntry::make('OutSide_comment')->label('Outside Comment '),
                        ])->columns(),
                        \Filament\Infolists\Components\Section::make([
                            TextEntry::make('InSide_date')->label('Inside Date')->dateTime(),
                            TextEntry::make('inSide_comment')->label('Inside Comment'),
                        ])->columns(),
                    ])->columns()
                ])->modalWidth(MaxWidth::SevenExtraLarge),
                Tables\Actions\Action::make('pdf')->tooltip('Print')->icon('heroicon-s-printer')->iconSize(IconSize::Medium)->label('')
                    ->url(fn($record) => route('pdf.takeOut', ['id' => $record->id]))->openUrlInNewTab(),
            ])
            ->bulkActions([
                ExportBulkAction::make()->after(function (){
                    if (Auth::check()) {
                        activity()
                            ->causedBy(Auth::user())
                            ->withProperties([
                                'action' => 'export',
                            ])
                            ->log('Export' . 'Gate Pass');
                    }
                })->exports([
                    ExcelExport::make()->askForFilename("Gate Pass")->withColumns([
                        Column::make('employee.fullName')->heading("Employee"),
                        Column::make('assets.product.title')->formatStateUsing(fn($record) => $record->assets->pluck('title')->toArray())->heading('Registered Asset'),
                        Column::make('itemsOut')->heading('Unregistered Asset')->formatStateUsing(function ($record) {
                            $data = [];
                            if ($record->itemsOut) {
                                foreach ($record->itemsOut as $item) {
                                    $data[] = $item['name'];
                                }
                            }
                            return $data;
                        }),
                        Column::make('from'),
                        Column::make('to'),
                        Column::make('date'),
                        Column::make('return_date'),
                        Column::make('mood'),
                        Column::make('status'),
                        Column::make('type'),
                        Column::make('gate_status')->heading('Gate Status'),
                    ]),
                ])->label('Export Gate Pass')->color('purple')
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTakeOuts::route('/'),
        ];
    }
}
