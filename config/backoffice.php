<?php

$configuredSuperAdmins = json_decode((string) env('BACKOFFICE_SUPER_ADMINS', ''), true);

return [
    'path' => env('BACKOFFICE_PATH', 'backoffice'),

    'super_admins' => is_array($configuredSuperAdmins) && $configuredSuperAdmins !== []
        ? $configuredSuperAdmins
        : [
            [
                'name' => 'Fernando Cardona Toro',
                'email' => 'fernandocardonatoro@gmail.com',
                'password' => env('BACKOFFICE_PRIMARY_SUPER_ADMIN_PASSWORD', '12345678'),
            ],
        ],
];
