<?php

namespace App\Filament\Pages;

use App\Models\Company;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Pages\Dashboard\Concerns\HasFilters;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Illuminate\Support\Carbon;


class CompanyOverView extends Page
{
    use HasFiltersForm;
    use HasFilters;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = "Comprehensive Report";
    protected static string $view = 'filament.pages.company-over-view';

    protected function getHeaderActions(): array
    {
        $months = collect([
            'Jan',
            'Feb',
            'Mar',
            'Apr',
            'May',
            'Jun',
            'Jul',
            'Aug',
            'Sep',
            'Oct',
            'Nov',
            'Dec'
        ]);

        return [
            Action::make('print')->label('Print')->url(function () {
                if (isset($this->filters['year'])) {
                    return route('filament.super-admin.pages.company-over-view');
                }
                return route('filament.super-admin.pages.company-over-view');
            })
        ];
    } 

    public function filtersForm(Form $form): Form
    {
        $currentYear = Carbon::now()->year;
        $years = array_combine(range($currentYear - 10, $currentYear + 1), range($currentYear - 10, $currentYear + 1));

        // dd();
        return $form->schema([
            Section::make([
                Select::make('year')
                    ->options($years)
                    ->live()
                    ->columnSpanFull()
                    ->default($currentYear)
                    ,
                    Select::make('company_id')
                    ->options(Company::all()->pluck('title','id'))
                    ->live()
                    ->columnSpanFull()
                    ->default($currentYear)
            ])->columns()
        ]);
    }
}
