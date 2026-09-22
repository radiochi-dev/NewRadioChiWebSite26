<?php

namespace App\Support\Backoffice;

final class BackofficePath
{
    public static function official(?string $suffix = null): string
    {
        return self::join(config('backoffice.official_prefix', 'backoffice'), $suffix);
    }

    public static function active(?string $suffix = null): string
    {
        return self::official($suffix);
    }

    private static function join(string $prefix, ?string $suffix = null): string
    {
        $base = '/'.trim($prefix, '/');

        if ($suffix === null || $suffix === '') {
            return $base;
        }

        return $base.'/'.ltrim($suffix, '/');
    }
}
