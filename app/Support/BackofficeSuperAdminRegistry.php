<?php

namespace App\Support;

use Illuminate\Support\Collection;

class BackofficeSuperAdminRegistry
{
    public static function all(): Collection
    {
        return collect(config('backoffice.super_admins', []))
            ->filter(fn (mixed $item): bool => is_array($item))
            ->map(fn (array $item): array => [
                'name' => (string) ($item['name'] ?? ''),
                'email' => mb_strtolower(trim((string) ($item['email'] ?? ''))),
                'password' => (string) ($item['password'] ?? ''),
            ])
            ->filter(fn (array $item): bool => $item['name'] !== '' && $item['email'] !== '' && $item['password'] !== '')
            ->values();
    }

    public static function findByEmail(string $email): ?array
    {
        return self::all()->firstWhere('email', mb_strtolower(trim($email)));
    }
}
