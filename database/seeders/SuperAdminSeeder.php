<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\BackofficeSuperAdminRegistry;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $hasPermissionRoles = Schema::hasTable('roles') && Schema::hasTable('model_has_roles');

        if ($hasPermissionRoles) {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
            Role::findOrCreate('super_admin', 'web');
        }

        BackofficeSuperAdminRegistry::all()->each(function (array $superAdmin) use ($hasPermissionRoles): void {
            $attributes = [
                'name' => $superAdmin['name'],
                'password' => Hash::make($superAdmin['password']),
                'email_verified_at' => now(),
            ];

            if (Schema::hasColumn('users', 'role')) {
                $attributes['role'] = 'SuperAdmin';
            }

            $user = User::query()->updateOrCreate(
                ['email' => $superAdmin['email']],
                $attributes,
            );

            if ($hasPermissionRoles) {
                $user->syncLegacyRoleToSpatieRole();
                $user->assignRole('super_admin');
            }
        });
    }
}
