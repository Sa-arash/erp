<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class AccountSettings extends Cluster
{
    protected static ?string $navigationLabel = 'Setting';
    protected static ?string $navigationIcon = 'heroicon-m-cog';
    protected static ?int $navigationSort = 8;
    protected static ?string $clusterBreadcrumb = 'Finance';
    protected static ?string $navigationGroup = 'Finance Management';
    protected static ?string $title = 'Setting';


}
