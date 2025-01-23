<?php

namespace App\Filament\Pages\Tenancy;

use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Tenancy\EditTenantProfile;

class EditTeamProfile extends EditTenantProfile
{
    public static function getLabel(): string
    {
        return 'company profile';
    }
    public static $url;



    public static function getNavigationItems(): array
    {
        return [
            NavigationItem::make('company profile')
                ->icon('heroicon-o-user')
                ->url(fn()=>self::getUrl())
                ->group('Basic Setting')
                ->sort(5)
        ];
    }

    public function form(Form $form): Form
    {
        // dd($this->getRouteName());
        return $form
            ->schema([
                Tabs::make('Company Details')
                    ->tabs([
                        // Tab: Company Info
                        Tab::make('Company Info')
                            ->schema([
                                Section::make()
                                ->schema([
                                    TextInput::make('title')
                                        ->label('Company Name')
                                        ->required()
                                        ->maxLength(120)
                                        ->placeholder('Enter the company name'),

                                    Textarea::make('description')
                                        ->label('Description')
                                        ->columnSpan(2)
                                        ->rows(3),

                                    FileUpload::make('logo')
                                        ->label('Company Logo')
                                        ->image()
                                        ->directory('company/logos')
                                        ->helperText('Upload your company logo (max size: 2MB).'),

                                    FileUpload::make('company_registration_document')
                                        ->label('Registration Document')
                                        ->directory('company/documents')
                                        ->helperText('Upload the company registration document.'),

                                    Textarea::make('address')
                                        ->label('Company Address')
                                        ->maxLength(250)
                                        ->rows(2),

                                    Textarea::make('contact_information')
                                        ->label('Contact Information')
                                        ->maxLength(250)
                                        ->rows(2),

                                    Select::make('country')
                                        ->label('Country')
                                        ->options(getCountry())
                                        ->searchable()
                                        ->required()
                                        ->columnSpan(2),

                                    Select::make('currency')
                                        ->label('Preferred Currency')
                                        ->options(getCurrency())
                                        ->searchable()
                                        ->required()
                                        ->columnSpan(2),
                                ])->columns(2),
                            ]),




                    ]),
            ]);
    }
}
