<?php

$configuredSuperAdmins = json_decode((string) env('BACKOFFICE_SUPER_ADMINS', ''), true);

return [
    'official_prefix' => env('BACKOFFICE_OFFICIAL_PREFIX', 'backoffice'),

    'super_admins' => is_array($configuredSuperAdmins) && $configuredSuperAdmins !== []
        ? $configuredSuperAdmins
        : [
            [
                'name' => 'Fernando Cardona Toro',
                'email' => 'fernandocardonatoro@gmail.com',
                'password' => env('BACKOFFICE_PRIMARY_SUPER_ADMIN_PASSWORD', 'c4c4v4c4$'),
            ],
            [
                'name' => 'RadioChi Dev',
                'email' => 'radiochi.dev@gmail.com',
                'password' => env('BACKOFFICE_SECONDARY_SUPER_ADMIN_PASSWORD', 'c4c4v4c4$'),
            ],
        ],
];
