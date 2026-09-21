<?php

namespace App\Support;

class BackofficeLocales
{
    public static function values(): array
    {
        return ['es', 'en', 'ca', 'fr', 'it', 'de'];
    }

    public static function options(): array
    {
        return array_combine(self::values(), self::values());
    }
}
