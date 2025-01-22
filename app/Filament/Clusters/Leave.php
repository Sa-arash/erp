<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class Leave extends Cluster
{
    protected static ?string $navigationIcon = 'leave';
    protected static ?string $navigationGroup='Human Resource';

    public static function getNavigationBadge(): ?string
    {

        return \App\Models\Leave::query()->where('status','waiting')->where('company_id',getCompany()->id)->count();
    }
}
