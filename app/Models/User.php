<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, LogsActivity, Notifiable;

    public const SUPER_ADMIN_EMAIL = 'fernandocardonatoro@gmail.com';

    public const BACKOFFICE_ROLES = [
        'super_admin',
        'editor',
        'marketing',
        'readonly',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function hasBackofficeAccess(): bool
    {
        if ($this->supportsPermissionRoles() && $this->hasAnyRole(self::BACKOFFICE_ROLES)) {
            return true;
        }

        return $this->isLegacySuperAdmin();
    }

    public function isSuperAdmin(): bool
    {
        if ($this->supportsPermissionRoles() && $this->hasRole('super_admin')) {
            return true;
        }

        return $this->isLegacySuperAdmin();
    }

    public function isLegacySuperAdmin(): bool
    {
        if ($this->email === self::SUPER_ADMIN_EMAIL) {
            return true;
        }

        return Schema::hasColumn($this->getTable(), 'role') && $this->role === 'SuperAdmin';
    }

    public function canManageBackofficeContent(): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->supportsPermissionRoles()
            && $this->hasAnyRole(['editor', 'marketing']);
    }

    public function canViewBackofficeContent(): bool
    {
        return $this->hasBackofficeAccess();
    }

    public function canManageBackofficeUsers(): bool
    {
        return $this->isSuperAdmin();
    }

    public function primaryBackofficeRole(): ?string
    {
        if ($this->supportsPermissionRoles()) {
            $roles = $this->getRoleNames()->map(fn (string $role): string => (string) $role)->all();

            foreach (self::BACKOFFICE_ROLES as $role) {
                if (in_array($role, $roles, true)) {
                    return $role;
                }
            }
        }

        if ($this->isLegacySuperAdmin()) {
            return 'super_admin';
        }

        return self::mapLegacyRoleToSpatieRole($this->role);
    }

    public function syncLegacyRoleToSpatieRole(): void
    {
        if (! $this->supportsPermissionRoles()) {
            return;
        }

        $targetRole = $this->email === self::SUPER_ADMIN_EMAIL
            ? 'super_admin'
            : self::mapLegacyRoleToSpatieRole($this->role);

        if (! $targetRole || $this->hasRole($targetRole)) {
            return;
        }

        $this->assignRole($targetRole);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('users')
            ->logOnly(['name', 'email', 'role'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public static function mapLegacyRoleToSpatieRole(?string $legacyRole): ?string
    {
        return match (mb_strtolower((string) $legacyRole)) {
            'superadmin' => 'super_admin',
            'editor' => 'editor',
            'marketing' => 'marketing',
            'readonly', 'read_only' => 'readonly',
            default => null,
        };
    }

    public static function mapSpatieRoleToLegacyRole(?string $role): ?string
    {
        return match ($role) {
            'super_admin' => 'SuperAdmin',
            'editor' => 'Editor',
            'marketing' => 'Marketing',
            'readonly' => 'ReadOnly',
            default => null,
        };
    }

    private function supportsPermissionRoles(): bool
    {
        return Schema::hasTable('roles') && Schema::hasTable('model_has_roles');
    }
}
