<?php

namespace App\Filament\Admin\Widgets;

use App\Models\PurchaseRequestItem;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Section;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Models\Activity as ActivityModel;

class PurchaseItemHistory extends BaseWidget
{
    public $PRID;
    protected int|string|array $columnSpan = 'full';

    public function mount($PRID)
    {
        $this->PRID = $PRID;
    }

    public function table(Table $table): Table
    {
        $IDs = PurchaseRequestItem::query()->where('purchase_request_id', $this->PRID)->pluck('id')->toArray();

        return $table
            ->query(
                Activity::query()->where('subject_type', 'App\Models\PurchaseRequestItem')->whereIn('subject_id', $IDs)
            )->defaultSort('created_at','desc')
            ->columns([
                Tables\Columns\TextColumn::make('No')->rowIndex(),
                TextColumn::make('log_name')
                    ->badge()
                    ->colors(static::getLogNameColors())
                    ->label(__('filament-logger::filament-logger.resource.label.type'))
                    ->formatStateUsing(fn($state) => ucwords($state))
                    ->sortable(),

                TextColumn::make('event')
                    ->label(__('filament-logger::filament-logger.resource.label.event'))
                    ->sortable(),

                TextColumn::make('description')
                    ->label(__('filament-logger::filament-logger.resource.label.description'))
                    ->wrap(),

                TextColumn::make('subject_type')
                    ->label(__('filament-logger::filament-logger.resource.label.subject'))
                    ->formatStateUsing(function ($state, Model $record) {
                        /** @var \Spatie\Activitylog\Contracts\Activity&ActivityModel $record */
                        if (!$state) {
                            return '-';
                        }
                        return Str::of($state)->afterLast('\\')->headline() . ' # ' . $record->subject?->log ? $record->subject?->log : $record->subject_id;
                    }),

                TextColumn::make('causer.employee.fullName')->state(fn($record) => $record->causer?->employee?->fullName ? $record->causer?->employee?->fullName : $record->causer->name)
                    ->label(__('filament-logger::filament-logger.resource.label.user')),

                TextColumn::make('created_at')
                    ->label(__('filament-logger::filament-logger.resource.label.logged_at'))
                    ->dateTime()
                    ->sortable(),
            ])->actions([
                Tables\Actions\ViewAction::make()->modalWidth(MaxWidth::SixExtraLarge)->form([
                    Section::make()
                        ->columns()
                        ->visible(fn ($record) => $record->properties?->count() > 0)
                        ->schema(function (?Model $record) {
                            /** @var \Spatie\Activitylog\Contracts\Activity&ActivityModel $record */
                            $properties = $record->properties->except(['attributes', 'old']);

                            $schema = [];

                            if ($properties->count()) {
                                $schema[] = KeyValue::make('properties')->hintAction(\Filament\Forms\Components\Actions\Action::make('Get')->label('Get IP Details')->visible(function ($state){
                                    if (isset($state['ip'])){
                                        return true;
                                    }
                                    return  false;
                                })->url(function ($state){
                                    return 'https://whatismyipaddress.com/ip/'.$state['ip'];
                                }))
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
            ]);
    }

    protected static function getLogNameColors(): array
    {
        $customs = [];

        foreach (config('filament-logger.custom') ?? [] as $custom) {
            if (filled($custom['color'] ?? null)) {
                $customs[$custom['color']] = $custom['log_name'];
            }
        }

        return array_merge(
            (config('filament-logger.resources.enabled') && config('filament-logger.resources.color')) ? [
                config('filament-logger.resources.color') => config('filament-logger.resources.log_name'),
            ] : [],
            (config('filament-logger.models.enabled') && config('filament-logger.models.color')) ? [
                config('filament-logger.models.color') => config('filament-logger.models.log_name'),
            ] : [],
            (config('filament-logger.access.enabled') && config('filament-logger.access.color')) ? [
                config('filament-logger.access.color') => config('filament-logger.access.log_name'),
            ] : [],
            (config('filament-logger.notifications.enabled') && config('filament-logger.notifications.color')) ? [
                config('filament-logger.notifications.color') => config('filament-logger.notifications.log_name'),
            ] : [],
            $customs,
        );
    }
}
