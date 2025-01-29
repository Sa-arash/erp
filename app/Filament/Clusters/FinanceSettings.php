<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class FinanceSettings extends Cluster
{

    protected static ?string $navigationGroup = 'Finance Management';
    protected static ?string $navigationIcon = 'heroicon-m-cog';
    protected static ?string $title = 'Setting';

    protected static ?string $clusterBreadcrumb = 'Setting';
    protected static ?int $navigationSort = 5;
}
