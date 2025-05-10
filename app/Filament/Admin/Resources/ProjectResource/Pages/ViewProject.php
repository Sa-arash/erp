<?php

namespace App\Filament\Admin\Resources\ProjectResource\Pages;

use App\Filament\Admin\Resources\ProjectResource;
use App\Models\Employee;
use Filament\Actions;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewProject extends ViewRecord
{
    protected static string $resource = ProjectResource::class;
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Project Information')
                ->schema([
                    TextEntry::make('name'),
                    TextEntry::make('code'),
                    TextEntry::make('start_date')->date(),
                    TextEntry::make('end_date')->date(),
                    TextEntry::make('budget')->state(fn($record)=>number_format($record->budget,2).defaultCurrency()->symbol),
                    TextEntry::make('priority_level'),
                    TextEntry::make('employee.fullName')->label('Project Manager'),
                    TextEntry::make('tags'),
                    TextEntry::make('description')->columnSpanFull(),

                ])
                ->columns(3),
            Section::make('Team')
                ->schema([
                    TextEntry::make('members')->bulleted()->state(fn($record)=>Employee::query()->whereIn('id',$record->members)->pluck('fullName','id')->toArray()),

                ]),
        ]);
    }
}
