<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['super_admin', 'editor', 'marketing', 'readonly'] as $roleName) {
            Role::findOrCreate($roleName, 'web');
        }

        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'role')) {
            return;
        }

        $roleTable = config('permission.table_names.model_has_roles', 'model_has_roles');
        $rolePivotKey = config('permission.column_names.role_pivot_key') ?? 'role_id';
        $modelMorphKey = config('permission.column_names.model_morph_key', 'model_id');

        DB::table('users')
            ->select(['id', 'email', 'role'])
            ->orderBy('id')
            ->each(function (object $user) use ($roleTable, $rolePivotKey, $modelMorphKey): void {
                $mappedRole = $user->email === User::SUPER_ADMIN_EMAIL
                    ? 'super_admin'
                    : User::mapLegacyRoleToSpatieRole($user->role);

                if (! $mappedRole) {
                    return;
                }

                $roleId = Role::findByName($mappedRole, 'web')->getKey();

                DB::table($roleTable)->updateOrInsert([
                    $rolePivotKey => $roleId,
                    'model_type' => User::class,
                    $modelMorphKey => $user->id,
                ], []);
            });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::query()
            ->whereIn('name', ['super_admin', 'editor', 'marketing', 'readonly'])
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
