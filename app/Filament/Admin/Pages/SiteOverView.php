<?php

namespace App\Filament\Admin\Pages;

use App\Models\Account;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Pages\Dashboard\Concerns\HasFilters;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Illuminate\Support\Carbon;

class SiteOverView extends Page
{
    use HasPageShield;
    use HasFiltersForm;
    use HasFilters;

    protected static ?string $navigationIcon = 'heroicon-m-table-cells';
    protected static ?int $navigationSort = -2;
    protected static string $view = 'filament.admin.pages.site-over-view';
    protected static ?string $navigationLabel = "Comprehensive Report";
    protected static ?string $title = "Comprehensive Report";

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
                    return route('filament.admin.pages.site-over-view', ['tenant' => getCompany()->id, 'year' => $this->filters['year']]);
                }
                return route('filament.admin.pages.site-over-view', ['tenant' => getCompany()->id, 'year' => Carbon::now()->year]);
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
                    ->default($currentYear)->searchable()->label('Company')
            ])->columns()
        ]);
    }
}
