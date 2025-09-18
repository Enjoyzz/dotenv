<?php

use Enjoys\Dotenv\ValueTypeCasting;

if (!function_exists('env')) {
    /**
     * @template T
     * @param string $key
     * @param mixed|null $default
     * @param null|callable(mixed, string): T $callback
     * @return ($callback is null ? mixed : T)
     */
    function env(
        string $key,
        mixed $default = null,
        ?callable $callback = null,
    ): mixed {
        /** @var mixed $value */
        $value = getenv($key) !== false ? getenv($key) : $_ENV[$key] ?? $default;

        $callback ??= [ValueTypeCasting::class, 'castType'];

        return $callback($value, $key);
    }
}
