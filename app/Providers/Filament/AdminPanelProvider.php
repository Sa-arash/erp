<?php

namespace App\Providers\Filament;

use App\Filament\Admin\Resources\AssetResource;
use App\Filament\Admin\Resources\ChequeResource;
use App\Filament\Admin\Resources\EmployeeResource\Pages\ViewEmployee;
use App\Filament\Admin\Resources\InvoiceResource;
use App\Filament\Admin\Resources\PartiesResource;
use App\Filament\Pages\Tenancy\EditTeamProfile;
use App\Filament\Resources\EmployeeResource;
use App\Models\Cheque;
use BezhanSalleh\FilamentShield\Middleware\SyncShieldTenant;
use App\Models\Company;
use App\Models\FinancialPeriod;
use App\Models\Invoice;
use App\Models\Account;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages;
use Filament\Panel;
use Filament\Support\Facades\FilamentAsset;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\Widgets;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{

    public function panel(Panel $panel): Panel
    {
        $reportNavigationItems = [];
        $financialPeriod =
            FinancialPeriod::query()->where('company_id', getCompanyUrl())->where('status', 'During')->first();
        if ($financialPeriod) {
            $reportNavigationItems = [

                // NavigationItem::make()
                //     ->icon('heroicon-o-document-text')
                //     ->label('Journal')
                //     ->url(fn() => route('pdf.jornal', [
                //         'period' => $financialPeriod ?? ' '
                //     ]))
                //     ->group('Accounting Report')
                //     ->sort(1),

                // NavigationItem::make()
                //     ->icon('heroicon-o-document-text')
                //     ->label('Subsidiary Leadger')
                //     ->url(fn() => route('pdf.account', [
                //         'period' => $financialPeriod ?? ' ',
                //         'reportTitle' => 'Subsidiary Leadger',
                //         'account' => implode('-', getCompany()->accounts->where('level', 'subsidiary')->pluck('id')->toArray()),
                //     ]))
                //     ->group('Accounting Report')
                //     ->sort(2),

                // NavigationItem::make()
                //     ->icon('heroicon-o-document-text')
                //     ->label('General Leadger')
                //     ->url(fn() => route('pdf.account', [
                //         'period' => $financialPeriod ?? ' ',
                //         'reportTitle' => 'General Leadger',
                //         'account' => implode('-', getCompany()->accounts->where('level', 'general')->pluck('id')->toArray()),
                //     ]))
                //     ->group('Accounting Report')
                //     ->sort(3),

                NavigationItem::make()
                    ->icon('heroicon-o-document-text')
                    ->label('Trial Balance')
                    ->url(fn() => route('pdf.trialBalance', [
                        'period' => $financialPeriod->id,
                    ]))
                    ->group('Accounting Report')
                    ->visible(fn() => isset($financialPeriod) && $financialPeriod != null)
                    ->sort(4),

                NavigationItem::make()
                    ->icon('heroicon-o-document-text')
                    ->label('Balance Sheet')
                    ->url(fn() => route('pdf.balance', [
                        'period' => $financialPeriod->id,
                    ]))
                    ->visible(fn() => isset($financialPeriod) && $financialPeriod != null)
                    ->group('Accounting Report')
                    ->sort(5),

                NavigationItem::make()
                    ->icon('heroicon-o-document-text')
                    ->label('Profit&Loss Report')
                    ->url(function () use ($financialPeriod) {
                        $accountsID = getCompany()->accounts->whereIn('stamp', ['Income', 'Expenses'])->pluck('id')->toArray();
                        $accounts = Account::query()->whereIn('id', $accountsID)->orWhereIn('parent_id', $accountsID)
                            ->orWhereHas('account', function ($query) use ($accountsID) {
                                return $query->whereIn('parent_id', $accountsID)->orWhereHas('account', function ($query) use ($accountsID) {
                                    return $query->whereIn('parent_id', $accountsID);
                                });
                            })
                            ->get()->pluck('id')->toArray();
                        // dd($accounts);
                        return route('pdf.account',
                            [
                                'period' => $financialPeriod->id,
                                'reportTitle' => 'Profit And Loss',
                                'account' => implode('-', $accounts)
                            ]);


                    })
                    ->visible(fn() => isset($financialPeriod) && $financialPeriod != null)
                    ->group('Accounting Report')
                    ->sort(5),

            ];


        }


        return $panel
            ->id('admin')->maxContentWidth(MaxWidth::Full)->favicon(asset('img/my.png'))
            ->path('admin')->sidebarCollapsibleOnDesktop()
            ->login()
            ->font(
                'Inter',
                url: asset('css/app/custom-stylesheet.css'),
                provider: LocalFontProvider::class,
            )
            ->colors([
                'primary' => Color::Sky,
                'aColor' => Color::Cyan,
                'view' => Color::Yellow,
                'edit' => Color::Green,
                'purple' => Color::Purple,
            ])
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\\Filament\\Admin\\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\\Filament\\Admin\\Pages')
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\\Filament\\Admin\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            ->userMenuItems([
                MenuItem::make()
                    ->label('Profile')->icon('heroicon-c-user-circle')->url(fn() => EmployeeResource::getUrl('view', ['record' => auth()->user()?->employee?->id ? auth()->user()?->employee?->id : 1])),

            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])->userMenuItems([
                MenuItem::make()
                    ->label('Settings')->visible(fn() => session('superAdminLogin') !== null)
                    ->url(fn() => session('superAdminLogin') !== null ? route('super.admin.login') : '')
                    ->icon('heroicon-o-cog-6-tooth'),
            ])
            ->tenantMiddleware([
                SyncShieldTenant::class,
            ], isPersistent: true)
            ->plugins([
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),
            ])
            ->tenantProfile(EditTeamProfile::class)
            ->navigationItems([
                //     // NavigationGroup::make('Website')
                //     // ->items([
                //     //     ...PageResource::getNavigationItems(),
                //     //     ...CategoryResource::getNavigationItems(),
                //     //     ...HomePageSettings::getNavigationItems(),
                //     // ]),
                NavigationItem::make()
                    ->label('Profile')->icon('heroicon-c-user-circle')->url(fn() => EmployeeResource::getUrl('view', ['record' => auth()->user()?->employee->id])),

                //         // Invoice
                //             NavigationItem::make()
                //             ->icon('heroicon-o-document-text')
                //             ->label('Add Journal Entry')->url(fn()=>InvoiceResource::getUrl('create'))
                //             ->group('Finance')
                //             ->parentItem('Journal Entry'),

                //             // Customers/Vendors
                //             NavigationItem::make()
                //             ->icon('heroicon-o-document-text')
                //             ->label('Add Customers/Vendors')->url(fn()=>PartiesResource::getUrl('create'))
                //             ->group('Finance')
                //             ->parentItem('Customers/Vendors'),
                //             NavigationItem::make()
                //             ->icon('heroicon-o-document-text')
                //             ->label('Customers')->url(fn()=>PartiesResource::getUrl('index').'?tableFilters[type][value]=customer')
                //             ->group('Finance')
                //             ->parentItem('Customers/Vendors'),
                //             NavigationItem::make()
                //             ->icon('heroicon-o-document-text')
                //             ->label('Vendors')->url(fn()=>PartiesResource::getUrl('index').'?tableFilters[type][value]=vendor')
                //             ->group('Finance')
                //             ->parentItem('Customers/Vendors'),

                //             // Cheque
                //             NavigationItem::make()
                //             ->icon('heroicon-o-document-text')
                //             ->label('Received Cheque List')->url(fn()=>ChequeResource::getUrl('index').'?tableFilters[status][value]=cleared')
                //             ->group('Finance')
                //             ->parentItem('Cheque Management'),
                //             NavigationItem::make()
                //             ->icon('heroicon-o-document-text')
                //             ->label('Issued Cheque List')->url(fn()=>ChequeResource::getUrl('index').'?tableFilters[status][value]=issued')
                //             ->group('Finance')
                //             ->parentItem('Cheque Management'),
//                NavigationItem::make()
//                    ->icon('heroicon-o-document-text')->sort(0)
////                    ->visible(fn() => isset($financialPeriod)===false)
//
//                    ->label('Add Assets list')
//                    ->url(fn() => AssetResource::getUrl('create'))
//                    ->group('Logistic management'),


                ...$reportNavigationItems,


                ...EditTeamProfile::getNavigationItems()
                // ...PayRoll::getNavigationItems()
            ])->navigationGroups([
                'Profile',
                'Human Resource',
                'Finance',
                'Accounting Report',
                'Logistic management',
            ])
            ->databaseNotifications()->tenant(Company::class, 'id', 'company');
    }
}
