<?php

use Enjoys\Dotenv\Env;

if (!function_exists('env')) {
    /**
     * @template T
     * @param string $key
     * @param mixed|null $default
     * @param null|callable(mixed): T $callback
     * @return ($callback is null ? mixed : T)
     */
    function env(string $key, mixed $default = null, ?callable $callback = null): mixed
    {
        return Env::get($key, $default, $callback);
    }
}
