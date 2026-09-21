<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->updateOrCreate(
            ['email' => 'fernandocardonatoro@gmail.com'],
            [
                'name' => 'Fernando Cardona Toro',
                'password' => Hash::make('12345678'),
                'role' => 'SuperAdmin',
                'email_verified_at' => now(),
            ]
        );

        if (Schema::hasTable('roles') && Schema::hasTable('model_has_roles')) {
            app(PermissionRegistrar::class)->forgetCachedPermissions();

            Role::findOrCreate('super_admin', 'web');
            $user->syncLegacyRoleToSpatieRole();
        }
    }
}
