<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ApprovalResource\Pages;
use App\Filament\Admin\Resources\ApprovalResource\RelationManagers;
use App\Models\Approval;
use App\Models\Employee;
use App\Models\Product;
use App\Models\PurchaseRequestItem;
use Filament\Forms;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Infolists\Components\Fieldset;
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

class ApprovalResource extends Resource
{
    protected static ?string $model = Approval::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-badge';


    public static function table(Table $table): Table
    {
        return $table->query(Approval::query()->where('employee_id', getEmployee()->id)->orderBy('id', 'desc'))
            ->columns([
                Tables\Columns\TextColumn::make('approvable.employee.info')->label('Employee')->searchable()->badge(),
                Tables\Columns\TextColumn::make('created_at')->label('Request Date')->date()->sortable(),
                Tables\Columns\TextColumn::make('approvable_type')->label('Request Type')->state(function ($record) {
                    return substr($record->approvable_type, 11);
                })->searchable()->badge(),
                Tables\Columns\TextColumn::make('approve_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('comment')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('approvable_type')->label('Request Type')->options(function () {
                    $data = [];
                    $approvals = Approval::query()->where('company_id', getCompany()->id)->distinct()->get()->unique('approvable_type');
                    foreach ($approvals as  $item) {
                        $data[$item->approvable_type] = substr($item->approvable_type, 11);
                    }
                    return $data;
                })->searchable()
            ], getModelFilter())
            ->actions([
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

                Tables\Actions\Action::make('viewVisitorRequest')->label('View')->visible(fn($record) => substr($record->approvable_type, 11) === "VisitorRequest")->infolist(function ($record) {
                    return [
                        Fieldset::make('Visitor Access')->schema([
                            Section::make('Visit’s Details')->schema([
                                TextEntry::make('requested_by')->label('Requested By'),
                                TextEntry::make('visit_date')->label('Visit Date'),
                                TextEntry::make('arrival_time')->label('Arrival Time'),
                                TextEntry::make('departure_time')->label('Departure Time'),
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


                        ])->columns(2)->relationship('approvable')->columns()
                    ];
                }),

                Action::make('viewPurchaseRequest')->label('View')->modalWidth(MaxWidth::Full)->infolist(function () {
                    return [
                        Fieldset::make('PR')->relationship('approvable')->schema([
                            TextEntry::make('employee.info'),
                            TextEntry::make('request_date')->date(),
                            TextEntry::make('purchase_number')->label('PR NO')->badge(),
                            TextEntry::make('description')->columnSpanFull()->label('Description'),
                            RepeatableEntry::make('items')->schema([
                                TextEntry::make('product.info')->badge(),
                                TextEntry::make('unit.title')->badge(),
                                TextEntry::make('quantity'),
                                TextEntry::make('estimated_unit_cost')->numeric(),
                                TextEntry::make('project.name')->badge(),
                                TextEntry::make('description')->columnSpanFull(),
                                TextEntry::make('head_decision')->badge()->label('Head Of Department Decision'),
                                TextEntry::make('head_comment')->tooltip(fn($record) => $record->head_comment)->label('Head Of Department Comment')->badge(),
                                TextEntry::make('ceo_decision')->badge()->label('CEO Decision'),
                                TextEntry::make('ceo_comment')->tooltip(fn($record) => $record->ceo_comment)->badge()->label('CEO Comment'),
                            ])->columns(5)->columnSpanFull(),
                            RepeatableEntry::make('approvals')->schema([
                                TextEntry::make('employee.fullName'),
                                TextEntry::make('created_at')->label('Request Date')->date(),
                                TextEntry::make('status')->badge(),
                                TextEntry::make('comment')->badge(),
                                TextEntry::make('approve_date')->date(),
                            ])->columns(5)->columnSpanFull()
                        ])->columns(3),

                    ];
                })->visible(fn($record) => substr($record->approvable_type, 11) === "PurchaseRequest"),

                Tables\Actions\Action::make('ApprovePurchaseRequest')->tooltip('ApprovePurchaseRequest')->label('Approve')->icon('heroicon-o-check-badge')->iconSize(IconSize::Large)->color('success')->form([
                    Forms\Components\Section::make([
                        Forms\Components\Section::make([
                            Select::make('employee')->disabled()->default(fn($record) => $record->approvable?->employee_id)->options(fn($record) => Employee::query()->where('id', $record->approvable?->employee_id)->get()->pluck('info', 'id'))->searchable(),
                            Forms\Components\ToggleButtons::make('status')->default('Approve')->colors(['Approve' => 'success', 'NotApprove' => 'danger', 'Pending' => 'primary'])->options(['Approve' => 'Approve', 'Pending' => 'Pending', 'NotApprove' => 'NotApprove'])->grouped(),
                            Forms\Components\ToggleButtons::make('is_quotation')->required()->label('Need Quotation')->boolean(' With Quotation', 'With out Quotation')->grouped()->inline(),
                            Forms\Components\Textarea::make('comment')->nullable()->columnSpanFull(),
                        ])->columns(3),
                        Forms\Components\Repeater::make('items')->formatStateUsing(fn($record) => $record->approvable?->items?->toArray())->schema([
                            Select::make('product_id')
                                ->label('Product')->options(function () {
                                    $products = getCompany()->products;
                                    $data = [];
                                    foreach ($products as $product) {
                                        $data[$product->id] = $product->title . " (" . $product->sku . ")";
                                    }
                                    return $data;
                                })->required()->searchable()->preload(),
                            TextInput::make('description')->label('Description')->required(),
                            Select::make('unit_id')->searchable()->preload()->label('Unit')->options(getCompany()->units->pluck('title', 'id'))->required(),
                            TextInput::make('quantity')->required()->live()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
                            TextInput::make('estimated_unit_cost')->label('Estimated Unit Cost')->live()->numeric()->mask(RawJs::make('$money($input)'))->stripCharacters(','),
                            Select::make('project_id')->searchable()->preload()->label('Project')->options(getCompany()->projects->pluck('name', 'id')),
                            Placeholder::make('total')->content(fn($state, Get $get) => number_format(((int)str_replace(',', '', $get('quantity'))) * ((int)str_replace(',', '', $get('estimated_unit_cost'))))),
                            Placeholder::make('stock in')->content(function ($record, Get $get) {
                                $products = Product::find($get('product_id'))->assets->where('status', 'inStorageUsable')->count();
                                $url = AssetResource::getUrl('index', ['tableFilters[product_id][value]' => $get('product_id'), 'tableFilters[status][value]' => 'inStorageUsable']);
                                return new HtmlString("<a style='color: #1cc6b9' target='_blank' href='{$url}'>$products</a>");
                            }),
                            TextInput::make('comment')->columnSpan(6),
                            Forms\Components\ToggleButtons::make('decision')->grouped()->inline()->columnSpan(2)->options(['approve' => 'Approve', 'reject' => 'Reject'])->required()->colors(['approve' => 'success', 'reject' => 'danger']),
                        ])->columns(8)->columnSpanFull()->addable(false)
                    ])->columns(),
                ])->modalWidth(MaxWidth::Full)->action(function ($data, $record) {

                    $record->update(['comment' => $data['comment'], 'status' => $data['status'], 'approve_date' => now()]);

                    if ($record->position === "CEO") {
                        $record->approvable->update(['is_quotation' => $data['is_quotation'], 'status' => "FinishedCeo"]);
                    } else {
                        $record->approvable->update(['is_quotation' => $data['is_quotation'], 'status' => 'FinishedHead']);
                    }
                    foreach ($data['items'] as $item) {
                        $item['status'] = $item['decision'] === "reject" ? "rejected" : "approve";
                        if ($record->position === "CEO") {
                            $item['ceo_comment'] = $item['comment'];
                            $item['ceo_decision'] = $item['decision'];
                        } else {
                            $item['head_comment'] = $item['comment'];
                            $item['head_decision'] = $item['decision'];
                        }
                        $prItem = PurchaseRequestItem::query()->firstWhere('id', $item['id']);
                        $prItem->update($item);
                    }
                    if ($data['status'] === "Approve") {
                        $CEO = Employee::query()->firstWhere('user_id', getCompany()->user_id);
                        if ($record->position !== "CEO") {
                            $record->approvable->approvals()->create([
                                'employee_id' => $CEO->id,
                                'company_id' => getCompany()->id,
                                'position' => 'CEO',
                                'status' => "Pending"
                            ]);
                        }
                    }
                })->visible(function ($record) {
                    if ($record->status->name !== "Approve") {
                        if (substr($record->approvable_type, 11) === "PurchaseRequest") {
                            return true;
                        }
                    }
                    return  false;
                }),
                Tables\Actions\Action::make('approve')->hidden(function ($record) {
                    if (substr($record->approvable_type, 11) === "PurchaseRequest") {
                        return true;
                    }
                })->icon('heroicon-o-check-badge')->iconSize(IconSize::Large)->color('success')->form([
                    Forms\Components\ToggleButtons::make('status')->default('Approve')->colors(['Approve' => 'success', 'NotApprove' => 'danger', 'Pending' => 'primary'])->options(['Approve' => 'Approve', 'Pending' => 'Pending', 'NotApprove' => 'NotApprove'])->grouped(),
                    Forms\Components\Textarea::make('comment')->nullable()
                ])->action(function ($data, $record) {
                    $record->update(['comment' => $data['comment'], 'status' => $data['status'], 'approve_date' => now()]);
                    $company = getCompany();
                    if (substr($record->approvable_type, 11) === "PurchaseRequest") {
                        if ($record->position === "Head Department") {
                            $CEO = Employee::query()->firstWhere('user_id', $company->user_id);
                            $record->approvals()->create([
                                'employee_id' => $CEO->id,
                                'company_id' => $company->id,
                                'position' => 'CEO',
                                'status' => "Pending"
                            ]);
                            $record->approvable->update([
                                'status' => 'FinishedHead'
                            ]);
                        } else {
                            $record->approvable->update([
                                'status' => 'FinishedCeo'
                            ]);
                        }
                    }
                    Notification::make('success')->success()->title($data['status'])->send();
                })->requiresConfirmation()->visible(fn($record) => $record->status->name === "Pending")
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    public static function getNavigationBadge(): ?string
    {
        return Approval::query()->where('employee_id', getEmployee()->id)->where('status', 'Pending')->count();
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
            //            'create' => Pages\CreateApproval::route('/create'),
            //            'edit' => Pages\EditApproval::route('/{record}/edit'),
        ];
    }
}
