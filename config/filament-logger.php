<?php
return [
    'datetime_format' => 'd/m/Y H:i A',
    'date_format' => 'd/m/Y',

    'activity_resource' => \Z3d0X\FilamentLogger\Resources\ActivityResource::class,
	'scoped_to_tenant' => false,
	'navigation_sort' => null,

    'resources' => [
        'enabled' => true,
        'log_name' => 'Resource',
        'logger' => \Z3d0X\FilamentLogger\Loggers\ResourceLogger::class,
        'color' => 'success',

        'exclude' => [
            \App\Filament\Admin\Resources\PurchaseRequestResource::class,
        ],
        'cluster' => null,
        'navigation_group' =>'IT Management',
    ],

    'access' => [
        'enabled' => true,
        'logger' => \Z3d0X\FilamentLogger\Loggers\AccessLogger::class,
        'color' => 'danger',
        'log_name' => 'Access',
    ],

    'notifications' => [
        'enabled' => true,
        'logger' => \Z3d0X\FilamentLogger\Loggers\NotificationLogger::class,
        'color' => null,
        'log_name' => 'Notification',
    ],

    'models' => [
        'enabled' => true,
        'log_name' => 'Model',
        'color' => 'warning',
        'logger' => \Z3d0X\FilamentLogger\Loggers\ModelLogger::class,
        'register' => [
            //App\Models\User::class,
        ],
    ],

    'export' => [
         [
             'log_name' => 'Export',
             'color' => 'primary',
         ]
    ],
    'custom' => [
         [
             'log_name' => 'Custom',
             'color' => 'primary',
         ]
    ],
];
