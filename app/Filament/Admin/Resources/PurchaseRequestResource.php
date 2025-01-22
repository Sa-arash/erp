<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PurchaseRequestResource\Pages;
use App\Filament\Admin\Resources\PurchaseRequestResource\RelationManagers;
use App\Models\PurchaseRequest;
use App\Models\Structure;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconSize;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\Rules\Unique;

class PurchaseRequestResource extends Resource
{
    protected static ?string $model = PurchaseRequest::class;

    protected static ?string $navigationGroup = 'Stock Management';

    protected static ?string $navigationIcon = 'heroicon-c-document-arrow-down';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('')->schema([
                    Forms\Components\Select::make('employee_id')->live()
                        ->searchable()
                        ->preload()
                        ->label('Requested By')
                        ->required()
                        ->options(getCompany()->employees->pluck('fullName', 'id')),

                    Forms\Components\TextInput::make('purchase_number')
                        ->label('PR Number')
                        ->unique(ignoreRecord: true, modifyRuleUsing: function (Unique $rule) {
                            return $rule->where('company_id', getCompany()->id);
                        })
                        ->unique('purchase_requests', 'purchase_number')
                        ->required()
                        ->numeric(),

                    Forms\Components\DatePicker::make('request_date')
                        ->default(now())
                        ->label('Request Date')
                        ->required(),



                    Forms\Components\Hidden::make('status')
                        ->label('Status')
                        // ->options([
                        //     'requested' => 'Requested',
                        //     'warehouse_checked' => 'Warehouse Checked',
                        //     'department_manager_approved' => 'Department Manager Approved',
                        //     'department_manager_rejected' => 'Department Manager Rejected',
                        //     'ceo_approved' => 'CEO Approved',
                        //     'ceo_rejected' => 'CEO Rejected',
                        //     'purchased' => 'Purchased',
                        //     'not_purchased' => 'Not Purchased',
                        // ])
                        ->default('requested')
                        ->required(),



                    Forms\Components\Select::make('department_id')
                        ->searchable()
                        ->preload()
                        ->label('Department')
                        ->options(getCompany()->departments->pluck('title', 'id'))
                        ->required(),


                    Select::make('structure_id')->searchable()->label('Location')

                        ->options(function (Get $get) {
                            return Structure::where('id', getCompany()->employees?->find($get('employee_id'))?->structure_id)->pluck('title', 'id');
                        })->required()->live(),


                    // SelectTree::make('structure_id')
                    // ->searchable()
                    // ->preload()
                    //     ->label('Location')
                    //     ->options(getCompany()->structures->pluck('title', 'id'))
                    //     ->required(),

                    Forms\Components\TextInput::make('description')
                        ->label('Description'),

                    Forms\Components\Hidden::make('company_id')
                        ->default(Filament::getTenant()->id)
                        ->required(),

                    Repeater::make('Requested Items')
                        ->relationship('items')
                        ->schema([
                            Forms\Components\Select::make('product_id')->label('Product')->options(function () {
                                $products = getCompany()->products;
                                $data = [];
                                foreach ($products as $product) {
                                    $data[$product->id] = $product->title . " (" . $product->sku . ")";
                                }
                                return $data;
                            })->required()->searchable()->preload(),

                            Forms\Components\TextInput::make('description')
                                ->label('Description')
                                ->required(),

                            Forms\Components\Select::make('unit_id')
                                ->searchable()
                                ->preload()
                                ->label('Unit')
                                ->options(getCompany()->units->pluck('title', 'id'))
                                ->required(),
                            Forms\Components\TextInput::make('quantity')
                                ->required()
                                ->mask(RawJs::make('$money($input)'))
                                ->stripCharacters(','),

                            Forms\Components\TextInput::make('estimated_unit_cost')
                                ->label('Estimated Unit Cost')
                                ->numeric()
                                ->mask(RawJs::make('$money($input)'))
                                ->stripCharacters(','),

                            Forms\Components\Select::make('project_id')
                                ->searchable()
                                ->preload()
                                ->label('Project')
                                ->options(getCompany()->projects->pluck('name', 'id')),


                            // Forms\Components\Select::make('warehouse_decision')
                            //     ->label('Warehouse Decision')
                            //     ->options([
                            //         'available_in_stock' => 'Available in Stock',
                            //         'needs_purchase' => 'Needs Purchase',
                            //     ])
                            //     ->default('needs_purchase')
                            //     ->required(),

                            // Forms\Components\Select::make('status')
                            //     ->label('Status')
                            //     ->options([
                            //         'purchased' => 'Purchased',
                            //         'assigned' => 'Assigned',
                            //         'not_purchased' => 'Not Purchased',
                            //         'rejected' => 'Rejected',
                            //     ])
                            //     ->default('not_purchased')
                            //     ->required(),


                            Forms\Components\Hidden::make('company_id')
                                ->default(Filament::getTenant()->id)
                                ->required(),
                        ])
                        ->columns(6)
                        ->columnSpanFull(),
                ])->columns(2)


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('purchase_number')->label('PR NO')->searchable(),

                    Tables\Columns\TextColumn::make('request_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('employee.fullName')
                    ->searchable(),
                Tables\Columns\TextColumn::make('department.title')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('total'),
                Tables\Columns\TextColumn::make('warehouse_decision')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('warehouse_status_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('department_manager_status_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('ceo_status_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('purchase_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),


            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('prPDF')->label('PR PDF')->iconSize(IconSize::Large)->icon('heroicon-s-printer')->url(fn($record)=>route('pdf.purchase',['id'=>$record->id])),
                Tables\Actions\Action::make('prQuotation')->color('warning')->label('Qu PDF')->iconSize(IconSize::Large)->icon('heroicon-s-printer')->url(fn($record)=>route('pdf.quotation',['id'=>$record->id])),
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
//                Tables\Actions\Action::make('Watehouse Check')->modalWidth(MaxWidth::Full)
//
//                    ->form(function ($record) {
//
//                        return [
//                            Repeater::make('items')->formatStateUsing(fn($record) => $record->items->toArray())
//                                ->schema([
//                                    Forms\Components\Select::make('product_id')
//                                        ->searchable()
//                                        ->preload()
//                                        ->label('Product')
//                                        ->options(getCompany()->products->pluck('title', 'id'))
//                                        ->required(),
//
//                                    Forms\Components\TextInput::make('description')
//                                        ->label('Description')
//                                        ->required(),
//
//                                    Forms\Components\Select::make('unit_id')
//                                        ->searchable()
//                                        ->preload()
//                                        ->label('Unit')
//                                        ->options(getCompany()->units->pluck('title', 'id'))
//                                        ->required(),
//                                    Forms\Components\TextInput::make('quantity')
//                                        ->required()
//                                        ->mask(RawJs::make('$money($input)'))
//                                        ->stripCharacters(','),
//
//                                    Forms\Components\TextInput::make('estimated_unit_cost')
//                                        ->label('Estimated Unit Cost')
//                                        ->numeric()
//                                        ->mask(RawJs::make('$money($input)'))
//                                        ->stripCharacters(','),
//
//                                    Forms\Components\Select::make('project_id')
//                                        ->searchable()
//                                        ->preload()
//                                        ->label('Project')
//                                        ->options(getCompany()->projects->pluck('name', 'id')),
//
//
//                                    Forms\Components\Select::make('warehouse_decision')
//                                        ->label('Warehouse Decision')
//                                        ->options([
//                                            'available_in_stock' => 'Available in Stock',
//                                            'needs_purchase' => 'Needs Purchase',
//                                        ])
//                                        ->default('needs_purchase')
//                                        ->required(),
//
//                                    // Forms\Components\Select::make('status')
//                                    //     ->label('Status')
//                                    //     ->options([
//                                    //         'purchased' => 'Purchased',
//                                    //         'assigned' => 'Assigned',
//                                    //         'not_purchased' => 'Not Purchased',
//                                    //         'rejected' => 'Rejected',
//                                    //     ])
//                                    //     ->default('not_purchased')
//                                    //     ->required(),
//
//
//                                    Forms\Components\Hidden::make('company_id')
//                                        ->default(Filament::getTenant()->id)
//                                        ->required(),
//                                ])->columns(7)
//                        ];
//                    })->action(function (array $data,   $record): void {
//                      $record->items->delete();
//                            $record->items->create($data['items']);
//
//
//
//                    })
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
            RelationManagers\ItemsRelationManager::class,
            RelationManagers\QuotationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchaseRequests::route('/'),
            'create' => Pages\CreatePurchaseRequest::route('/create'),
            'edit' => Pages\EditPurchaseRequest::route('/{record}/edit'),
            'view' => Pages\ViewPurcheseRequest::route('/{record}/view'),
        ];
    }
}
