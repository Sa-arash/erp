<?php

namespace App\Filament\Admin\Resources\PurchaseRequestResource\RelationManagers;

use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity as ActivityModel;

class ItemsRelationManager extends RelationManager
{
    protected static ?string $label = 'Item';
    protected static string $relationship = 'items';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')->label('Product/Service')->options(function () {
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
                    ->label('EUC')
                    ->numeric()
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters(','),

                Forms\Components\Select::make('project_id')
                    ->searchable()
                    ->preload()
                    ->label('Project')
                    ->options(getCompany()->projects->pluck('name', 'id')),

                Forms\Components\Hidden::make('company_id')
                    ->default(Filament::getTenant()->id)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        $approves=$this->ownerRecord->approvals->where('approve_date','!=',null)->all();

        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('product.sku')->label('SKU'),
                Tables\Columns\TextColumn::make('product.title')->label('Product/Service'),
                Tables\Columns\TextColumn::make('description'),
                Tables\Columns\TextColumn::make('document')->url(function ($record){
                    if (isset($record->media[0])){
                        return $record->media[0]->original_url;
                    }
                })->state(fn($record)=> isset($record->media[0])? 'Attach File':null)->color('warning')->alignCenter(),


                Tables\Columns\TextColumn::make('unit.title'),
                Tables\Columns\TextColumn::make('quantity'),
                Tables\Columns\TextColumn::make('estimated_unit_cost')->state(fn($record)=>$record->estimated_unit_cost.$this->ownerRecord->currency?->symbol)->label('EUC')->numeric(),
                Tables\Columns\TextColumn::make('total')->state(fn ($record) => $record->estimated_unit_cost * $record->quantity.$this->ownerRecord->currency?->symbol)->numeric(),
                Tables\Columns\TextColumn::make('project.name'),
                Tables\Columns\TextColumn::make('clarification_commenter')->state(isset($approves[0])? $approves[0]?->employee?->fullName:"")->label('Warehouse Commenter'),
                Tables\Columns\TextColumn::make('clarification_decision')->state(fn($record)=>match ($record->clarification_decision){
                    'approve' => 'Approved',
                    'reject' => 'Rejected',
                    default => 'Pending',
                })->color(fn (string $state): string => match ($state) {
                    'Approved' => 'success',
                    'Rejected' => 'danger',
                    default => 'primary',
                })->label('Warehouse Decision')->alignCenter()->badge(),
                Tables\Columns\TextColumn::make('clarification_comment')->label('Warehouse  Comment'),
                Tables\Columns\TextColumn::make('verification_commenter')->state(isset($approves[1])? $approves[1]?->employee?->fullName:"")->label('Verified by '),
                Tables\Columns\TextColumn::make('verification_decision')->state(fn($record)=>match ($record->verification_decision){
                    'approve' => 'Approved',
                    'reject' => 'Rejected',
                    default => 'Pending',
                })->color(fn (string $state): string => match ($state) {
                    'Approved' => 'success',
                    'Rejected' => 'danger',
                    default => 'primary',
                })->label('Verification Decision')->alignCenter()->badge(),
                Tables\Columns\TextColumn::make('verification_comment')->label('Verification Comment'),
                Tables\Columns\TextColumn::make('approval_commenter')->state(isset($approves[2])? $approves[2]?->employee?->fullName:"")->label('Approved by'),
                Tables\Columns\TextColumn::make('approval_decision')->state(fn($record)=>match ($record->approval_decision){
                    'approve' => 'Approved',
                    'reject' => 'Rejected',
                    default => 'Pending',
                })->color(fn (string $state): string => match ($state) {
                    'Approved' => 'success',
                    'Rejected' => 'danger',
                    default => 'primary',
                })->label('Approval Decision')->alignCenter()->badge(),
                Tables\Columns\TextColumn::make('approval_comment')->label('Approval Comment'),
                Tables\Columns\TextColumn::make('status')->visible(count($approves) ===3),

            ])

            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make()->label('View History')->slideOver()->color('warning')->modalWidth(MaxWidth::SixExtraLarge)->form([
                    Forms\Components\Repeater::make('activities')->relationship('activities')->schema([
                        Section::make([TextInput::make('causer_id')
                            ->afterStateHydrated(function ($component, ?Model $record) {
                                /** @phpstan-ignore-next-line */
                                return $component->state($record->causer?->employee?->fullName ? $record->causer?->employee?->fullName : $record->causer->name);
                            })
                            ->label('Employee'),
                            TextInput::make('created_at')
                                ->afterStateHydrated(function ($component, ?Model $record) {
                                    /** @phpstan-ignore-next-line */
                                    return $component->state(Carbon::make($record->created_at)->format('Y/m/d h:i A'));
                                })
                                ->label('Date'),])->columns(),
                        Section::make()
                            ->columns()
                            ->visible(fn($record) => $record->properties?->count() > 0)
                            ->schema(function (?Model $record) {
                                /** @var \Spatie\Activitylog\Contracts\Activity&ActivityModel $record */
                                $properties = $record->properties->except(['attributes', 'old']);

                                $schema = [];

                                if ($properties->count()) {
                                    $schema[] = KeyValue::make('properties')
                                        ->label(__('filament-logger::filament-logger.resource.label.properties'))
                                        ->columnSpan('full');
                                }

                                if ($old = $record->properties->get('old')) {
                                    $schema[] = KeyValue::make('old')
                                        ->afterStateHydrated(fn (KeyValue $component) => $component->state($old))
                                        ->label(__('filament-logger::filament-logger.resource.label.old'));
                                }

                                if ($attributes = $record->properties->get('attributes')) {
                                    $schema[] = KeyValue::make('attributes')
                                        ->afterStateHydrated(fn (KeyValue $component) => $component->state($attributes))
                                        ->label(__('filament-logger::filament-logger.resource.label.new'));
                                }

                                return $schema;
                            }),
                    ])
                ])

//                Tables\Actions\DeleteAction::make(),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
