<?php

namespace App\Filament\Admin\Resources\EmployeeResource\Pages;

use App\Filament\Admin\Resources\EmployeeResource;
use Filament\Resources\Pages\Page;

class Manager extends Page
{
    protected static string $resource = EmployeeResource::class;

    protected static string $view = 'filament.admin.resources.employee-resource.pages.manager';
}
