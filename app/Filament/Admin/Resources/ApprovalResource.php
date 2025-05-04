<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ApprovalResource\Pages;
use App\Filament\Admin\Resources\ApprovalResource\RelationManagers;
use App\Models\Approval;
use App\Models\Employee;
use App\Models\Product;
use App\Models\PurchaseRequestItem;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconSize;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class ApprovalResource extends Resource implements HasShieldPermissions
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
            'PR Warehouse (1)',
            'PR Verification (2)',
            'PR Approval (3)'
        ];
    }

    public static function table(Table $table): Table
    {
        return $table->query(Approval::query()->where('employee_id', getEmployee()->id)->orderBy('id', 'desc'))
            ->columns([
                Tables\Columns\TextColumn::make('approvable.employee.info')->label('Employee')->searchable()->badge(),
                Tables\Columns\TextColumn::make('created_at')->label('Request Date')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('approvable_type')->label('Request Type')->state(function ($record) {
                    $type = substr($record->approvable_type, 11);
                    if ($type === "Separation") {
                        return "Clearance";
                    }
                    return $type;

                })->searchable()->badge(),
                Tables\Columns\TextColumn::make('approve_date')->label('Approval Date')->date()->sortable(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('comment')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('Status')->options(['Approve'=>'Approve','NotApprove'=>'NotApprove','Pending'=>'Pending'])->searchable(),
            ], getModelFilter())
            ->actions([
                Tables\Actions\Action::make('viewLeave')->visible(fn($record) => substr($record->approvable_type, 11) === "Leave")->infolist(function ($record){
                    return [
                      Fieldset::make('')->schema([
                          TextEntry::make('employee.info')->label('Employee'),
                          TextEntry::make('typeLeave.title')->label('Leave Type'),
                          TextEntry::make('start_leave')->date()->label('Start Leave'),
                          TextEntry::make('end_leave')->date()->label('End Leave'),
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

                Tables\Actions\Action::make('viewTakeOut')->visible(fn($record) => substr($record->approvable_type, 11) === "TakeOut")->infolist(function ($record) {
                    return [
                        Fieldset::make('Take Out')->schema([
                            TextEntry::make('employee.info')->label('Employee'),
                            TextEntry::make('from')->label('From'),
                            TextEntry::make('to')->label('To'),
                            TextEntry::make('reason')->label('Reason'),
                            TextEntry::make('date')->label('Date'),
                            TextEntry::make('status')->label('Status'),
                            TextEntry::make('type')->label('Type'),
                            RepeatableEntry::make('items')->getStateUsing(function () use ($record) {
                                return $record->approvable->items;
                            })->schema([
                                TextEntry::make('asset.title'),
                                TextEntry::make('remarks'),
                            ])->columnSpanFull()->columns()
                        ])->relationship('approvable')->columns()
                    ];
                }),
                Tables\Actions\Action::make('viewVisitorRequest')->label('View')->visible(fn($record) => substr($record->approvable_type, 11) === "VisitorRequest")->infolist(function () {
                    return [
                        Fieldset::make('Visitor Access')->schema([
                            Section::make('Visitor Access Request')->schema([
                                Section::make('Visit’s Details')->schema([
                                    TextEntry::make('employee.info')->label('Requested By'),
                                    TextEntry::make('visit_date')->date()->label('Visit Date'),
                                    TextEntry::make('arrival_time')->time()->label('Arrival Time'),
                                    TextEntry::make('departure_time')->time()->label('Departure Time'),
                                    TextEntry::make('purpose')->label('Purpose')->columnSpanFull(),
                                ])->columns(4),

                                RepeatableEntry::make('visitors_detail')
                                    ->label('Visitors Detail')
                                    ->schema([
                                        TextEntry::make('name')->label('Full Name'),
                                        TextEntry::make('id')->label('ID/Passport'),
                                        TextEntry::make('phone')->label('Phone'),
                                        TextEntry::make('organization')->label('Organization'),
                                        TextEntry::make('type')->label('Type'),
                                        TextEntry::make('remarks')->label('Remarks'),
                                    ])->columns(6)->columnSpanFull(),

                                RepeatableEntry::make('driver_vehicle_detail')
                                    ->label('Drivers/Vehicles Detail')
                                    ->schema([
                                        TextEntry::make('name')->label('Full Name'),
                                        TextEntry::make('id')->label('ID/Passport'),
                                        TextEntry::make('phone')->label('Phone'),
                                        TextEntry::make('model')->label('Model'),
                                        TextEntry::make('color')->label('Color'),
                                        TextEntry::make('Registration_Plate')->label('Registration Plate'),
                                    ])->columns(6)->columnSpanFull(),
                                ImageEntry::make('file')->label('File Upload')->state(function ($record){
                                    if ($record?->media){
                                        return $record?->media?->where('collection_name','attachment')->first()?->original_url;
                                    }
                                })

                            ])->columns(2)
                        ])->relationship('approvable')->columns()
                    ];
                })->modalWidth(MaxWidth::SevenExtraLarge),
                Action::make('viewPurchaseRequest')->label('View')->modalWidth(MaxWidth::Full)->infolist(function () {
                    return [
                        Fieldset::make('PR')->relationship('approvable')->schema([
                            RepeatableEntry::make('approvals')->schema([
                                TextEntry::make('employee.fullName')->label(fn($record)=>$record->employee?->position?->title),
                                TextEntry::make('created_at')->label('Request Date')->dateTime(),
                                TextEntry::make('status')->badge(),
                                TextEntry::make('comment')->tooltip(fn($record) => $record->comment)->limit(50),
                                TextEntry::make('approve_date')->dateTime(),
                            ])->columns(5)->columnSpanFull()
                        ])->columns(3),

                    ];
                })->visible(fn($record) => substr($record->approvable_type, 11) === "PurchaseRequest"),


                Tables\Actions\Action::make('approve')->hidden(function ($record) {
                    if (substr($record->approvable_type, 11) === "PurchaseRequest" or substr($record->approvable_type, 11) === "Loan") {
                        return true;
                    }
                })->icon('heroicon-o-check-badge')->iconSize(IconSize::Large)->color('success')->form([
                    Forms\Components\ToggleButtons::make('status')->default('Approve')->colors(['Approve' => 'success', 'NotApprove' => 'danger', 'Pending' => 'primary'])->options(['Approve' => 'Approve', 'Pending' => 'Pending', 'NotApprove' => 'NotApprove'])->grouped(),
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
                    }elseif (substr($record->approvable_type, 11) === "TakeOut"){
                        if ($data['status'] === "Approve") {
                                $record->approvable->update([
                                    'mood' => 'Approved'
                                ]);
                        }else{
                            $record->approvable->update([
                                'mood' => 'NotApproved'
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
                    }
                    Notification::make('success')->success()->title($data['status'])->send();
                })->requiresConfirmation()->visible(fn($record) => $record->status->name === "Pending"),
                Tables\Actions\Action::make('loanApprove')->visible(fn($record)=>substr($record->approvable_type, 11) === "Loan" and $record->status->value ==="Pending")->label('Approve Loan')->color('success')->form([
                   Forms\Components\Section::make([
                       Forms\Components\ToggleButtons::make('status')->live()->columnSpanFull()->default('Approve')->colors(['Approve' => 'success', 'NotApprove' => 'danger', 'Pending' => 'primary'])->options(['Approve' => 'Approve', 'Pending' => 'Pending', 'NotApprove' => 'NotApprove'])->grouped(),
                       Forms\Components\Textarea::make('comment')->columnSpanFull()->nullable(),
                       TextInput::make('amount')->label('Loan Amount')->mask(RawJs::make('$money($input)'))->stripCharacters(',')->required(fn(Get $get)=>$get('status')!=='NotApprove')->numeric(),
                       TextInput::make('number_of_installments')->label('Number of Installments')->required(fn(Get $get)=>$get('status')!=='NotApprove')->numeric(),
                       Forms\Components\DatePicker::make('first_installment_due_date')->required(fn(Get $get)=>$get('status')!=='NotApprove')->columnSpanFull()->label('First Installment Due Date')->afterOrEqual(now())

                   ])->columns()
                ])->action(function ($data,$record){
                    $record->update([
                        'status'=>$data['status'],
                        'comment'=>$data['comment'],
                        'approve_date'=>now()
                    ]);
                    if ($data['status']==="Approve"){
                        $record->approvable->update([
                            'first_installment_due_date'=>$data['first_installment_due_date'],
                            'number_of_installments'=>$data['number_of_installments'],
                            'amount'=>$data['amount'],
                            'status'=>'ApproveManager'
                        ]);
                    }elseif ($data['status']==="NotApprove"){
                        $record->approvable->update([
                            'status'=>'rejected'
                        ]);
                    }
                    Notification::make('success')->title('Success Submitted')->success()->send();
                })->requiresConfirmation()->modalWidth(MaxWidth::TwoExtraLarge),
                Action::make('viewLoan')->visible(fn($record)=>substr($record->approvable_type, 11) === "Loan" and $record->status->value ==="Pending")->infolist([
                        Fieldset::make('')->relationship('approvable')->schema([
                            TextEntry::make('loan_code')->label('Loan Code'),
                            TextEntry::make('request_date')->dateTime()->label('Request Date'),
                            TextEntry::make('request_amount')->numeric()->label('Request Amount'),
                            TextEntry::make('description')->columnSpanFull()->label('Description'),
                        ])
                ]),
                Action::make('url')->visible(function ($record){
                    if ($record->status->name !== "Approve") {
                        if (substr($record->approvable_type, 11) === "PurchaseRequest") {
                            return true;
                        }
                    }
                    return  false;
                })->label('Items')->url(fn($record)=>ApprovalResource::getUrl('purchase',['record'=>$record->id]))
            ])
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
            'purchase'=>Pages\ApprovePurchase::route('/purchase/{record}')
            //            'create' => Pages\CreateApproval::route('/create'),
            //            'edit' => Pages\EditApproval::route('/{record}/edit'),
        ];
    }
}
