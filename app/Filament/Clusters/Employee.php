<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class Employee extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-s-user-group';
    protected static ?string $clusterBreadcrumb = 'Human Resource';
    protected static ?string $navigationLabel='Employee';
    protected static ?string $title='Employee';
    protected static ?string $navigationGroup='Human Resource';



}
