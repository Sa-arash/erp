<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ApprovalResource\Pages;
use App\Filament\Admin\Resources\ApprovalResource\RelationManagers;
use App\Models\Approval;
use App\Models\Asset;
use App\Models\AssetEmployeeItem;
use App\Models\Holiday;
use App\Models\Leave as ModelLeave;
use App\Models\TakeOutItem;
use App\Models\Unit;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconSize;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use OpenSpout\Common\Entity\Style\CellAlignment;

class   ApprovalResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Approval::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-badge';

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'PR Warehouse',
            'PO Logistic Head',
            'PR Verification',
            'PO Verification',
            'PR Approval',
            'PO Approval'
        ];
    }

    public static function table(Table $table): Table
    {
        return $table->query(Approval::query()->where('employee_id', getEmployee()->id))->defaultSort('status','desc')
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex()->label('No'),
                Tables\Columns\TextColumn::make('approvable.employee.fullName')->label('Employee')->description(function ($record) {
                    if (substr($record->approvable_type, 11) === "PurchaseRequest") {
                        return  new HtmlString('PR-ATGT/UNC/'.$record->approvable->purchase_number.' <br>'. Str::limit($record->approvable->description, 70));
                    }
                })->tooltip(function ($record){
                    if (substr($record->approvable_type, 11) === "PurchaseRequest") {
                        return  $record->approvable->description;
                    }
                })->width(300),
                Tables\Columns\TextColumn::make('created_at')->label('Request Date')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('approve_date')->label('Approval Date')->date(),
                Tables\Columns\TextColumn::make('comment')->sortable(),
                Tables\Columns\TextColumn::make('status')->state(fn($record) => match ($record->status->value) {
                    'Approve' => 'Approved',
                    'NotApprove' => 'Rejected',
                    'Pending' => 'Pending',
                })->color(fn($state) => match ($state) {
                    'Approved' => 'success',
                    'Rejected' => 'danger',
                    'Pending'=>'info',
                })->label('Status')->badge(),
            ])

            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('Status')->options(['Approve'=>'Approve','NotApprove'=>'NotApproved','Pending'=>'Pending'])->searchable(),
                Tables\Filters\SelectFilter::make('department')->searchable()->preload()->label('Department')->options(getCompany()->departments()->pluck('title','id'))->query(fn($query,$data)=>isset($data['value'])? $query->whereHas('approvable',function ($query)use($data){
                    return $query->whereHas('employee',function ($query)use($data){
                            return $query->where('department_id',$data['value']);
                        });
                }):$query),
            ], getModelFilter())


            ->actions([
                Tables\Actions\Action::make('viewLeave')->visible(fn($record) => substr($record->approvable_type, 11) === "Leave")->infolist(function ($record){
                    return [
                      Fieldset::make('')->schema([
                          TextEntry::make('employee.info')->label('Employee'),
                          TextEntry::make('typeLeave.title')->label('Leave Type'),
                          TextEntry::make('start_leave')->date()->label('Start Leave'),
                          TextEntry::make('end_leave')->date()->label('End Leave'),
                          TextEntry::make('end_leave')->state(function ($record){
                              $start = Carbon::parse($record->start_leave);

                              $end = Carbon::parse($record->end_leave);
                              $period = CarbonPeriod::create($start, $end);
                              $daysBetween = $period->count(); // تعداد کل روزها
                              $CompanyHoliday = count(getDaysBetweenDates($start, $end, getCompany()->weekend_days));

                              $holidays = Holiday::query()->where('company_id', getCompany()->id)->whereBetween('date', [$start, $end])->count();
                              $validDays = $daysBetween - $holidays-$CompanyHoliday;
                              return $validDays;
                          })->label('Days'),
                          TextEntry::make('Total Leave('.now()->format('Y').")")->state(function ()use($record){
                              $leaves= ModelLeave::query()->where('employee_id',$record->employee_id)->whereBetween('start_leave', [now()->startOfYear(), now()->endOfYear()])->whereBetween('end_leave', [now()->startOfYear(), now()->endOfYear()])->where('status','accepted')->sum('days');
                              return new HtmlString("<div style='font-size: 25px !important;'>  <span style='color: red;font-size: 25px !important;'>$leaves</span> Days </div>");
                          }),
                          TextEntry::make('is_circumstances')->state(fn($record)=>$record?->approvable?->is_circumstances?"Yes":"No")->label('Aware of any Circumstances'),
                          TextEntry::make('explain_leave')->label('Explain'),
                      ])->columns()->relationship('approvable')
                    ];
                }),
                Tables\Actions\Action::make('viewOvertime')->visible(fn($record) => substr($record->approvable_type, 11) === "Overtime")->infolist(function ($record){
                    return [
                        Fieldset::make('')->schema([
                            TextEntry::make('employee.info')->label('Employee'),
                            TextEntry::make('title')->label('Description'),
                            TextEntry::make('overtime_date')->date()->label('Start Leave'),
                            TextEntry::make('hours')->label('Hours'),
                        ])->columns()->relationship('approvable')
                    ];
                }),

                Tables\Actions\Action::make('viewGatePass')->visible(fn($record) => substr($record->approvable_type, 11) === "TakeOut")->infolist(function ($record) {
                    return [
                        Fieldset::make('Gate Pass')->schema([
                            TextEntry::make('employee.info')->label('Employee'),
                            TextEntry::make('from')->label('From'),
                            TextEntry::make('to')->label('To'),
                            TextEntry::make('reason')->label('Reason'),
                            TextEntry::make('date')->label('Date'),
                            TextEntry::make('status')->label('Status'),
                            TextEntry::make('type')->label('Type'),
                            RepeatableEntry::make('items')->schema([
                                TextEntry::make('asset.description'),
                                TextEntry::make('asset.number')->label('Asset Number'),
                                TextEntry::make('status')->color(fn ($state)=>match ($state){
                                    'Approved'=>'success','Not Approved'=>'danger','Pending'=>'primary'
                                })->badge(),
                                TextEntry::make('remarks'),
                            ])->columnSpanFull()->columns(4),
                            RepeatableEntry::make('itemsOut')->label('Unregistered Asset')->schema([
                                TextEntry::make('name'),
                                TextEntry::make('quantity'),
                                TextEntry::make('unit'),
                                TextEntry::make('status')->color(fn ($state)=>match ($state){
                                    'Approved'=>'success','Not Approved'=>'danger','Pending'=>'primary'
                                })->badge(),
                                TextEntry::make('remarks'),
                            ])->columnSpanFull()->columns(4),
                        ])->relationship('approvable')->columns()
                    ];
                })->modalWidth(MaxWidth::SevenExtraLarge),
                Tables\Actions\Action::make('viewVisitorRequest')->url(fn($record)=>VisitorRequestResource::getUrl('view',['record'=>$record->approvable_id]))->label('View')->visible(fn($record) => substr($record->approvable_type, 11) === "VisitorRequest")
                    ->modalWidth(MaxWidth::SevenExtraLarge),
                Action::make('viewPurchaseRequest')->label('View')->modalWidth(MaxWidth::Full)->infolist(function () {
                    return [
                        Fieldset::make('Approvals')->relationship('approvable')->schema([
                            RepeatableEntry::make('approvals')->label('')->schema([
                                ImageEntry::make('employee.image')->circular()->label('')->state(fn($record)=>$record->employee->media->where('collection_name','images')->first()?->original_url),
                                TextEntry::make('employee.fullName')->label(fn($record)=>$record->employee?->position?->title),
                                TextEntry::make('created_at')->label('Request Date')->dateTime(),
                                TextEntry::make('status')->state(fn($record)=>match ($record->status->value){
                                    'Approve'=>'Approved',
                                    'Not Approve'=>'Not Approved',
                                    'Pending'=>'Pending',
                                })->badge()->color(fn($state)=> match ($state){
                                    'Approved'=>'success',
                                    'Not Approved'=>'danger',
                                    'Pending'=>'primary',
                                }),
                                TextEntry::make('comment')->tooltip(fn($record) => $record->comment)->limit(50),
                                TextEntry::make('approve_date')->label('Approved Date')->dateTime(),
                                ImageEntry::make('employee.signature')->label('')->state(fn($record)=>$record->status->value==="Approve"? $record->employee->media->where('collection_name','signature')->first()?->original_url:''),
                            ])->columns(7)->columnSpanFull()
                        ])->columns(3),

                    ];
                })->visible(fn($record) => substr($record->approvable_type, 11) === "PurchaseRequest")->modalHeading('PR Approved by:'),
                Action::make('viewPurchaseOrder')->label('View')->modalWidth(MaxWidth::Full)->infolist(function () {
                    return [
                        Fieldset::make('PO Approvals')->relationship('approvable')->schema([
                            RepeatableEntry::make('approvals')->schema([
                                ImageEntry::make('employee.image')->circular()->label('')->state(fn($record)=>$record->employee->media->where('collection_name','images')->first()?->original_url),
                                TextEntry::make('employee.fullName')->label(fn($record)=>$record->employee?->position?->title),
                                TextEntry::make('created_at')->label('Request Date')->dateTime(),
                                TextEntry::make('status')->badge(),
                                TextEntry::make('comment')->tooltip(fn($record) => $record->comment)->limit(50),
                                TextEntry::make('approve_date')->dateTime(),
                                ImageEntry::make('employee.signature')->label('')->state(fn($record)=>$record->status->value==="Approve"? $record->employee->media->where('collection_name','signature')->first()?->original_url:''),
                            ])->columns(7)->columnSpanFull()
                        ])->columns(3),

                    ];
                })->visible(fn($record) => substr($record->approvable_type, 11) === "PurchaseOrder" and isset($record->approvable))->modalHeading('PO Approved by:'),
                Action::make('viewUrgent')->label('View Urgent Leave')->infolist([
                    Fieldset::make('Urgent')->relationship('approvable')->schema([
                            ImageEntry::make('employee.image')->circular()->label('')->state(fn($record)=>$record->employee->media->where('collection_name','images')->first()?->original_url),
                            TextEntry::make('employee.fullName')->label('Employee'),
                            TextEntry::make('employee.ID_number')->label('Badge Number'),
                            TextEntry::make('data')->dateTime(),
                            TextEntry::make('time_out')->time(),
                            TextEntry::make('time_in')->time(),
                            TextEntry::make('reason')->columnSpanFull(),
                    ])->columns(3),
                ])->visible(function ($record){
                    if (substr($record->approvable_type, 11) === "UrgentLeave") {
                        return true;
                    }
                    return  false;
                }),
                Action::make('approveGatePass')->visible(function ($record){
                    if (substr($record->approvable_type, 11) === "TakeOut" and $record->status->value==='Pending'){
                        return true;
                    }
                })->fillForm(function ($record){

                    return [
                        'items'=>$record->approvable?->items?->toArray(),
                      'itemsOut'=>$record->approvable->itemsOut
                    ];
                })->form([
                    Forms\Components\ToggleButtons::make('status')->afterStateUpdated(function (Forms\Set $set,$state,Get $get){
                        $items = $get('itemsOut') ?? [];
                        $assetItems = $get('items') ?? [];
                        if ($state=="NotApprove"){
                            $state="Not Approved";
                        }else{
                            $state="Approved";
                        }
                        foreach ($items as   &$item) {
                            $item['status'] = $state;
                        }
                        foreach ($assetItems as   &$item) {
                            $item['status'] = $state;
                        }

                        $set('itemsOut', $items);
                        $set('items', $assetItems);
                    })->required()->default('Approve')->live()->colors(['Approve' => 'success', 'NotApprove' => 'danger'])->options(['Approve' => 'Approve','NotApprove' => 'Not Approve'])->grouped(),
                    Forms\Components\Textarea::make('comment')->nullable(),
                    Repeater::make('items')->deletable(false)->addable(false)->label('Registered Asset')->addActionLabel('Add to Register Asset')->orderable(false)->schema([
                        Select::make('asset_id')->disabled()
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                            ->live()->label('Asset')->options(function () {
                                $data = [];
                                $assets=Asset::query()->with('product')->where('company_id',getCompany()->id)->get();
                                foreach ($assets as $asset) {
                                    $data[$asset->id] = $asset->product?->title." ".$asset->description . " ( SKU #" . $asset->product?->sku . " )";
                                }
                                return $data;
                            })->required()->searchable()->preload(),
                        TextInput::make('remarks')->nullable(),
                        Forms\Components\ToggleButtons::make('status')->grouped()->options(['Approved'=>'Approve','Not Approved'=>'Not Approve'])->default('Approve')->colors(['Approved'=>'success','Not Approved'=>'danger'])->required(),

                    ])->columnSpanFull()->columns(3),

                    Repeater::make('itemsOut')->label('Unregistered Asset')->addActionLabel('Add to UnRegister Asset')->orderable(false)->schema([
                        TextInput::make('name')->required()->readOnly(),
                        TextInput::make('quantity')->required()->readOnly(),
                        Select::make('unit')->disabled()->searchable()->options(Unit::query()->where('company_id', getCompany()->id)->pluck('title','title'))->required(),
                        TextInput::make('remarks')->nullable()->readOnly(),
                        Forms\Components\ToggleButtons::make('status')->grouped()->options(['Approved'=>'Approve','Not Approved'=>'Not Approve'])->default('Approve')->colors(['Approved'=>'success','Not Approved'=>'danger'])->required(),
                        FileUpload::make('image')->disabled()->columnSpanFull()->label('Image Upload')->image()->imageEditor(),
                    ])->columnSpanFull()->columns(5)->deletable(false)->addable(false)
                ])->modalWidth(MaxWidth::SevenExtraLarge)->action(function ($record ,$data){

                        if ($data['status'] === "Approve") {
                            if ($record->approvable->mood==="Pending"){
                                $record->approvable->approvals()->whereNot('id', $record->id)->where('position', 'Security')->delete();
                                $arrayData=[];
                                foreach ($record->approvable->itemsOut as $key=> $item){
                                    $data['itemsOut'][$key]['unit']=$item['unit'];
                                    $data['itemsOut'][$key]['image']=$item['image'];
                                    $item=$data['itemsOut'][$key];
                                    $arrayData[]=$item;
                                }

                                foreach ($data['items'] as  $asset){
                                    $item=TakeOutItem::query()->firstWhere('id',$asset['id']);
                                    $item->update(['status'=>$asset['status']]);
                                }

                                $record->approvable->update([
                                    'mood' => 'Approved',
                                    'itemsOut'=>$arrayData
                                ]);
                            }
                        }else{
                            $record->approvable->update([
                                'mood' => 'NotApproved'
                            ]);
                        }
                    $record->update(['comment' => $data['comment'], 'status' => $data['status'], 'approve_date' => now()]);


                })->icon('heroicon-o-check-badge')->iconSize(IconSize::Large)->color('success'),

                Tables\Actions\Action::make('approve')->hidden(function ($record) {
                    if (substr($record->approvable_type, 11) === "PurchaseRequest" or substr($record->approvable_type, 11) === "PurchaseOrder" or substr($record->approvable_type, 11) === "Loan"or substr($record->approvable_type, 11) === "Leave" or substr($record->approvable_type, 11) === "TakeOut"or substr($record->approvable_type, 11) === "Grn") {
                        return true;
                    }
                })->icon('heroicon-o-check-badge')->iconSize(IconSize::Large)->color('success')->form([
                    Forms\Components\ToggleButtons::make('status')->default('Approve')->colors(['Approve' => 'success', 'NotApprove' => 'danger'])->options(['Approve' => 'Approve','NotApprove' => 'Denied'])->grouped(),
                    Forms\Components\Textarea::make('comment')->nullable()
                ])->action(function ($data, $record) {
                    $record->update(['comment' => $data['comment'], 'status' => $data['status'], 'approve_date' => now()]);
                    if (substr($record->approvable_type, 11) === "VisitorRequest") {
                        if ($data['status'] === "Approve") {
                                $record->approvable->update([
                                    'status' => 'approved'
                                ]);
                        }else{
                            $record->approvable->update([
                                'status' => 'notApproved'
                            ]);
                        }
                    }elseif (substr($record->approvable_type, 11) === "Leave"){
                        if ($data['status'] === "Approve") {
                                $record->approvable->update([
                                    'status' => 'approveHead'
                                ]);
                        }else{
                            $record->approvable->update([
                                'status' => 'rejected'
                            ]);
                        }
                    }elseif (substr($record->approvable_type, 11) === "Overtime"){
                        if ($data['status'] === "Approve") {
                            $record->approvable->update([
                                'status' => 'approveHead'
                            ]);
                        }else{
                            $record->approvable->update([
                                'status' => 'rejected'
                            ]);
                        }
                    }elseif (substr($record->approvable_type, 11) === "UrgentLeave"){
                        if ($data['status'] === "Approve") {
                            $record->approvable->update([
                                'status' => 'approveHead'
                            ]);
                        }else{
                            $record->approvable->update([
                                'status' => 'rejected'
                            ]);
                        }
                    }
                    Notification::make('success')->success()->title('Answer '. substr($record->approvable_type, 11). ' Status : '.$data['status'])->send()->sendToDatabase(auth()->user());
                })->requiresConfirmation()->visible(fn($record) => $record->status->name === "Pending"),
                Tables\Actions\Action::make('approveLeave')->visible(function ($record) {
                    if (substr($record->approvable_type, 11) === "Leave" and $record->status->name === "Pending") {
                        return true;
                    }
                    return  false;
                })->icon('heroicon-o-check-badge')->iconSize(IconSize::Large)->color('success')->form([
                    Forms\Components\ToggleButtons::make('status')->live()->default('Approve')->colors(['Approve' => 'success', 'NotApprove' => 'danger'])->options(['Approve' => 'Approve','NotApprove' => 'Denied'])->grouped(),
                    Forms\Components\Textarea::make('comment')->label('Rationale')->visible(fn(Get $get)=> $get('status')=="NotApprove")->required()->maxLength(100)
                ])->action(function ($record,$data){
                    if (!isset($data['comment'])){
                        $data['comment']=null;
                    }
                    $record->update(['comment' => $data['comment'], 'status' => $data['status'], 'approve_date' => now()]);
                    if ($data['status'] === "Approve") {
                            $record->approvable->update([
                                'status' => 'approveHead'
                            ]);
                        }else{
                            $record->approvable->update([
                                'status' => 'rejected'
                            ]);
                        }
                })->requiresConfirmation(),
                Tables\Actions\Action::make('loanApprove')->visible(fn($record)=>substr($record->approvable_type, 11) === "Loan" and $record->status->value ==="Pending")->label('Approve Loan')->color('success')->form([
                   Forms\Components\Section::make([
                       Forms\Components\ToggleButtons::make('status')->live()->columnSpanFull()->default('Approve')->colors(['Approve' => 'success', 'NotApprove' => 'danger', 'Pending' => 'primary'])->options(['Approve' => 'Approve', 'Pending' => 'Pending', 'NotApprove' => 'NotApprove'])->grouped(),
                       Forms\Components\Textarea::make('comment')->columnSpanFull()->nullable(),

                   ])->columns()])->action(function ($data,$record){
                    $record->update([
                        'status'=>$data['status'],
                        'comment'=>$data['comment'],
                        'approve_date'=>now()
                    ]);
                    if ($data['status']==="Approve"){
                        $record->approvable->update([
                            'status'=>'ApproveManager'
                        ]);
                    }elseif ($data['status']==="NotApprove"){
                        $record->approvable->update([
                            'status'=>'rejected'
                        ]);
                    }
                    Notification::make('success')->title('Success Submitted')->success()->send();
                })->requiresConfirmation()->modalWidth(MaxWidth::TwoExtraLarge)->icon( fn($record)=>$record->status->value=='Approve'? 'heroicon-o-check-badge':'heroicon-o-x-circle')->iconSize(IconSize::Large)->color(fn($record)=>$record->status->value=='Approve'? 'success':'danger' ),
                Action::make('viewLoan')->visible(fn($record)=>substr($record->approvable_type, 11) === "Loan" )->infolist([
                        Fieldset::make('')->relationship('approvable')->schema([
                            TextEntry::make('loan_code')->label('Loan Code'),
                            TextEntry::make('request_date')->dateTime()->label('Request Date'),
                            TextEntry::make('employee.loan_limit')->state(fn($record)=>number_format($record->approvable->employee?->loan_limit).$record->employee?->currency?->symbol)->numeric()->label('Employee Loan Limit'),
                            TextEntry::make('request_amount')->state(fn($record)=>number_format($record->approvable->request_amount).$record->employee?->currency?->symbol)->numeric()->label('Request Amount'),
                            TextEntry::make('description')->columnSpanFull()->label('Description'),
                        ])
                ]),
                Action::make('revise')->color('warning')->iconSize(IconSize::Medium)->icon('heroicon-c-exclamation-circle')->label('Need Revise')->action(function ($record){
                    $record->approvable->update([
                        'need_change'=>1
                    ]);
                    Notification::make('success')->title('Submitted Successfully')->success()->send();
                })->requiresConfirmation()->visible(fn($record) => substr($record->approvable_type, 11) === "PurchaseRequest" and !$record->approvable?->need_change and $record->approvable?->status->value==='Requested'),
                Action::make('url')->visible(function ($record) {
                    if (substr($record->approvable_type, 11) === "PurchaseRequest" and isset($record->approvable)) {
                        return true;
                    }
                })->label('Item Approval ')->icon( fn($record)=>$record->status->value=='Approve'? 'heroicon-o-check-badge':'heroicon-o-x-circle')->iconSize(IconSize::Large)->color(fn($record)=>$record->status->value=='Approve'? 'success':'danger' )->url(fn($record)=>ApprovalResource::getUrl('purchase',['record'=>$record->id])),
                Action::make('urlOrder')->visible(function ($record) {
                    if (substr($record->approvable_type, 11) === "PurchaseOrder" and isset($record->approvable)) {
                        return true;
                    }
                })->label('Purchase Order Approve')->icon( fn($record)=>$record->status->value=='Approve'? 'heroicon-o-check-badge':'heroicon-o-x-circle')->iconSize(IconSize::Large)->color(fn($record)=>$record->status->value=='Approve'? 'success':'danger' )->url(fn($record)=>ApprovalResource::getUrl('purchase_order',['record'=>$record->id])),
                Action::make('urlOrder')->visible(function ($record) {
                    if (substr($record->approvable_type, 11) === "Grn" and isset($record->approvable)) {
                        return true;
                    }
                })->label('GRN Approve')->icon( fn($record)=>$record->status->value=='Approve'? 'heroicon-o-check-badge':'heroicon-o-x-circle')->iconSize(IconSize::Large)->color(fn($record)=>$record->status->value=='Approve'? 'success':'danger' )->url(fn($record)=>ApprovalResource::getUrl('grn',['record'=>$record->id])),
            ])->actionsAlignment(CellAlignment::LEFT)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    public static function getNavigationBadge(): ?string
    {
        return Approval::query()->where('employee_id', getEmployee()?->id)->where('status', 'Pending')->count() ?? 0;
    }
    public static function getNavigationBadgeColor(): string|array|null
    {
        return Approval::query()->where('employee_id', getEmployee()?->id)->where('read_at', null)->count() ? "danger":'info' ;
    }
    public static function getNavigationIcon(): string|Htmlable|null
    {
        return Approval::query()->where('employee_id', getEmployee()?->id)->where('read_at', null)->count() ? "dangerCheck":parent::getNavigationIcon() ;

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
            'index' => Pages\ListApprovals::route('/'),
            'purchase'=>Pages\ApprovePurchase::route('/purchase/{record}'),
            'purchase_order'=>Pages\ApprovePurchaseOrder::route('/purchase-order/{record}'),
            'grn'=>Pages\ApproveGRNItem::route('GRN/{record}')
            //            'create' => Pages\CreateApproval::route('/create'),
            //            'edit' => Pages\EditApproval::route('/{record}/edit'),
        ];
    }
}
