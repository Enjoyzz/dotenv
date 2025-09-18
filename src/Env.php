<?php

declare(strict_types=1);


namespace Enjoys\Dotenv;


final class Env
{
    /**
     * @template T
     * @param string $key
     * @param mixed|null $default
     * @param null|callable(mixed): T $callback
     * @return ($callback is null ? mixed : T)
     */
    public static function get(string $key, mixed $default = null, ?callable $callback = null): mixed
    {
        $value = getenv($key) !== false ? getenv($key) : $_ENV[$key] ?? $default;
        $callback ??= [ValueTypeCasting::class, 'castType'];
        return $callback($value);
    }
}
