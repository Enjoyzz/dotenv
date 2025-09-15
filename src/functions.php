<?php

use Enjoys\Dotenv\ValueTypeCasting;

if (!function_exists('env')) {
    function env($key, $default = null, ?callable $cast = null)
    {
        $cast = $cast ?? fn($value) => ValueTypeCasting::castType($value);
        $value = getenv($key) ?: $_ENV[$key] ?? $default;
        return $cast($value);
    }
}
