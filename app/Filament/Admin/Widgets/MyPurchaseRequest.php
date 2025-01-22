<?php

namespace App\Filament\Admin\Widgets;

use App\Models\PurchaseRequest;
use App\Models\Structure;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Validation\Rules\Unique;

class MyPurchaseRequest extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PurchaseRequest::query()->where('employee_id', auth()->user()->id)
            )
            ->columns([
                Tables\Columns\TextColumn::make('request_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('employee_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('purchase_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('department.title')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('structure.title')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status'),

                // Tables\Columns\TextColumn::make('warehouse_status_date')
                //     ->date()
                //     ->sortable(),

                // Tables\Columns\TextColumn::make('department_manager_status_date')
                //     ->date()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('ceo_status_date')
                //     ->date()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('purchase_date')
                //     ->date()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('created_at')
                //     ->dateTime()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
                // Tables\Columns\TextColumn::make('updated_at')
                //     ->dateTime()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),


            ])




            ->headerActions([
                Action::make('Request Purchase') ->modalWidth(MaxWidth::FitContent  )->form([
                    Section::make('')->schema([
                        Hidden::make('employee_id')->default(fn()=>auth()->user()->id)
                            ->required(),

                        TextInput::make('purchase_number')
                            ->label('PR Number')
                            ->unique(modifyRuleUsing: function (Unique $rule) {
                                return $rule->where('company_id', getCompany()->id);
                            })
                            ->unique('purchase_requests', 'purchase_number')
                            ->required()
                            ->numeric(),

                        DatePicker::make('request_date')
                            ->default(now())
                            ->label('Request Date')
                            ->required(),



                        Hidden::make('status')
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
                        Select::make('department_id')
                            ->searchable()
                            ->preload()
                            ->label('Department')
                            ->options(getCompany()->departments->pluck('title', 'id'))
                            ->required(),
                        Select::make('structure_id')->searchable()->label('Location')

                            ->options(function (Get $get) {
                                return Structure::where('id', (auth()->user()->employee?->structure_id))->pluck('title', 'id');
                            })->required()->live(),


                        // SelectTree::make('structure_id')
                        // ->searchable()
                        // ->preload()
                        //     ->label('Location')
                        //     ->options(getCompany()->structures->pluck('title', 'id'))
                        //     ->required(),

                        TextInput::make('description')
                            ->label('Description'),



                        Repeater::make('Requested Items')

                            ->schema([
                                Select::make('product_id')
                                    ->searchable()
                                    ->preload()
                                    ->label('Product')
                                    ->options(getCompany()->products->pluck('title', 'id'))
                                    ->required(),

                                TextInput::make('description')
                                    ->label('Description')
                                    ->required(),

                                Select::make('unit_id')
                                    ->searchable()
                                    ->preload()
                                    ->label('Unit')
                                    ->options(getCompany()->units->pluck('title', 'id'))
                                    ->required(),
                                TextInput::make('quantity')
                                    ->required()
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(','),

                                TextInput::make('estimated_unit_cost')
                                    ->label('Estimated Unit Cost')
                                    ->numeric()
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')->required(),

                                Select::make('project_id')
                                    ->searchable()
                                    ->preload()
                                    ->label('Project')
                                    ->options(getCompany()->projects->pluck('name', 'id')),


                                // Select::make('warehouse_decision')
                                //     ->label('Warehouse Decision')
                                //     ->options([
                                //         'available_in_stock' => 'Available in Stock',
                                //         'needs_purchase' => 'Needs Purchase',
                                //     ])
                                //     ->default('needs_purchase')
                                //     ->required(),

                                // Select::make('status')
                                //     ->label('Status')
                                //     ->options([
                                //         'purchased' => 'Purchased',
                                //         'assigned' => 'Assigned',
                                //         'not_purchased' => 'Not Purchased',
                                //         'rejected' => 'Rejected',
                                //     ])
                                //     ->default('not_purchased')
                                //     ->required(),

                            ])
                            ->columns(6)
                            ->columnSpanFull(),
                    ])->columns(2)
                ])->action(function ($data){

                    $data['company_id']=getCompany()->id;
                    $request= PurchaseRequest::query()->create($data);
                    foreach ($data['Requested Items'] as $requestedItem) {
                        $requestedItem['company_id']=getCompany()->id;
                        $request->items()->create($requestedItem);
                    }

                })
            ])

        ;
    }
}
