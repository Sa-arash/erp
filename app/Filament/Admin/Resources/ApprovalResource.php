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
                Tables\Columns\TextColumn::make('approvable_type')->state(function ($record) {
                    return substr($record->approvable_type, 11);
                })->searchable()->badge(),
                Tables\Columns\TextColumn::make('approvable_id')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('comment')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('approve_date')->dateTime()->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('ApprovePurchaseRequest')->tooltip('ApprovePurchaseRequest')->label('Approve')->icon('heroicon-o-check-badge')->iconSize(IconSize::Large)->color('success')->form([
                    Forms\Components\Section::make([
                        Select::make('employee')->disabled()->default(fn($record) => $record->approvable?->employee_id)->options(fn($record) => Employee::query()->where('id', $record->approvable?->employee_id)->get()->pluck('info', 'id'))->searchable(),
                        Forms\Components\ToggleButtons::make('status')->default('Approve')->colors(['Approve' => 'success', 'NotApprove' => 'danger', 'Pending' => 'primary'])->options(['Approve' => 'Approve', 'Pending' => 'Pending', 'NotApprove' => 'NotApprove'])->grouped(),
                        Forms\Components\Textarea::make('comment')->nullable(),
                        Forms\Components\Repeater::make('items')->formatStateUsing(fn($record) => $record->approvable->items->toArray())->schema([
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
                    $record->update([]);
                    foreach ($data['items'] as $item) {
                        $prItem=PurchaseRequestItem::query()->firstWhere('id',$item['id']);
                        $prItem->update($item);
                    }
                }),
                Tables\Actions\Action::make('approve')->icon('heroicon-o-check-badge')->iconSize(IconSize::Large)->color('success')->form([
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
