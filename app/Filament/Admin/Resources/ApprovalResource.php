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
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class ApprovalResource extends Resource
{
    protected static ?string $model = Approval::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-badge';


    public static function table(Table $table): Table
    {
        return $table->query(Approval::query()->where('employee_id', getEmployee()->id)->orderBy('id','desc'))
            ->columns([
                Tables\Columns\TextColumn::make('approvable.employee.info')->label('Employee')->searchable()->badge(),
                Tables\Columns\TextColumn::make('approvable_type')->label('Request Type')->state(function ($record) {
                    return substr($record->approvable_type, 11);
                })->searchable()->badge(),
                Tables\Columns\TextColumn::make('approvable_id')->action(Tables\Actions\Action::make('View')->infolist(function ($record){
                    if (substr($record->approvable_type, 11) ==="TakeOut"){
                        return [
                           Section::make([
                               TextEntry::make('employee_id')->state($record->approvable->employee->info)->label('Employee'),
                               TextEntry::make('to')->state($record->approvable->from)->label('From'),
                               TextEntry::make('from')->state($record->approvable->to)->label('To'),
                               TextEntry::make('reason')->state($record->approvable->reason)->label('Reason'),
                               TextEntry::make('date')->state($record->approvable->date)->label('Date'),
                               TextEntry::make('status')->state($record->approvable->status)->label('Status'),
                               TextEntry::make('type')->state($record->approvable->type)->label('Type'),
                               RepeatableEntry::make('items')->getStateUsing(function ()use($record){
                                   return $record->approvable->items;
                               })->schema([
                                   TextEntry::make('asset.title')->state(fn($record)=>$record->asset->title),
                                   TextEntry::make('remarks')->state(fn($record)=>$record->remarks),
                               ])->columnSpanFull()->columns()
                           ])->columns()
                        ];
                    }
                }))->numeric()->sortable(),
                Tables\Columns\TextColumn::make('comment')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('approve_date')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('approvable_type')->label('Request Type')->options(function (){
                    $data=[];
                    $approvals=Approval::query()->where('company_id',getCompany()->id)->distinct()->get()->unique('approvable_type');
                    foreach($approvals as  $item){
                        $data[$item->approvable_type]= substr($item->approvable_type,11);
                    }
                    return $data;
                })->searchable()
            ],getModelFilter())
            ->actions([
                Tables\Actions\Action::make('ApprovePurchaseRequest')->tooltip('ApprovePurchaseRequest')->label('Approve')->icon('heroicon-o-check-badge')->iconSize(IconSize::Large)->color('success')->form([
                    Forms\Components\Section::make([
                        Select::make('employee')->disabled()->default(fn($record) => $record->approvable?->employee_id)->options(fn($record) => Employee::query()->where('id', $record->approvable?->employee_id)->get()->pluck('info', 'id'))->searchable(),
                        Forms\Components\ToggleButtons::make('is_quotation')->required()->label('Need Quotation')->boolean(' With Quotation', 'With out Quotation')->grouped()->inline(),
                        Forms\Components\ToggleButtons::make('status')->default('Approve')->colors(['Approve' => 'success', 'NotApprove' => 'danger', 'Pending' => 'primary'])->options(['Approve' => 'Approve', 'Pending' => 'Pending', 'NotApprove' => 'NotApprove'])->grouped(),
                        Forms\Components\Textarea::make('comment')->nullable(),
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
                                $products = Product::find($get('product_id'))->assets->where('status', 'inStorageUsable');
                                $data = "";
                                foreach ($products as $product) {
                                    $url = AssetResource::getUrl('view', ['record' => $product->id]);
                                    $data .= "<a style='color: #1cc6b9 ' target='_blank' href='{$url}'>{$product->title}</a>";
                                }
                                return new HtmlString($products->count() . "<br>" . $data);
                            }),
                            TextInput::make('ceo_comment')->columnSpan(6),
                            Forms\Components\ToggleButtons::make('ceo_decision')->grouped()->inline()->columnSpan(2)->options(['approve' => 'Approve', 'reject' => 'Reject'])->required()->colors(['approve' => 'success', 'reject' => 'danger']),
                        ])->columns(8)->columnSpanFull()->addable(false)
                    ])->columns(),
                ])->modalWidth(MaxWidth::Full)->action(function ($data, $record) {
                    $record->update(['comment' => $data['comment'], 'status' => $data['status'], 'approve_date' => now()]);
                    $record->approvable->update(['is_quotation' => $data['is_quotation']]);
                    foreach ($data['items'] as $item) {
                        $prItem=PurchaseRequestItem::query()->firstWhere('id',$item['id']);
                        $prItem->update($item);
                    }
                })->visible(function ($record){
                    if ($record->status->name!=="Approve"){
                        if (substr($record->approvable_type, 11)==="PurchaseRequest"){
                            return true;
                        }
                    }
                    return  false;
                }),
                Tables\Actions\Action::make('approve')->hidden(function ($record){
                    if (substr($record->approvable_type, 11)==="PurchaseRequest"){
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
