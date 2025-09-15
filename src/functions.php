<?php

use Enjoys\Dotenv\ValueTypeCasting;

if (!function_exists('env')) {
    /**
     * @param string $key
     * @param mixed|null $default
     * @param callable|null $cast
     * @return mixed
     */
    function env(string $key, mixed $default = null, ?callable $cast = null): mixed
    {
        $cast = $cast ?? fn(mixed $value): mixed => ValueTypeCasting::castType($value);
        /** @var mixed $value */
        $value = getenv($key) !== false ? getenv($key) : $_ENV[$key] ?? $default;
        return $cast($value);
    }
}
