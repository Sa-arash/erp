<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class HrSettings extends Cluster
{
    protected static ?string $navigationGroup = 'HR Management System';
    protected static ?string $navigationIcon = 'heroicon-m-cog';
    protected static ?string $title = 'Basic Info';

    protected static ?string $clusterBreadcrumb = 'Setting';
    protected static ?int $navigationSort = 5;

}
