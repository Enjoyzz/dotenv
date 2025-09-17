<?php

use Enjoys\Dotenv\ValueTypeCasting;

if (!function_exists('env')) {
    /**
     * @template T
     * @param string $key
     * @param mixed|null $default
     * @param null|callable(mixed): T $transform
     * @param null|callable(mixed):bool $validator
     * @param bool $raw if true - skip transform and validation and return raw value
     * @return ($raw is true ? mixed : ($transform is null ? mixed : T))
     */
    function env(
        string $key,
        mixed $default = null,
        ?callable $transform = null,
        ?callable $validator = null,
        bool $raw = false
    ): mixed {

        /** @var mixed $value */
        $value = getenv($key) !== false ? getenv($key) : $_ENV[$key] ?? $default;

        if ($raw === true) {
            return $value;
        }

        $transform ??= fn(mixed $value): mixed => ValueTypeCasting::castType($value);
        $value = $transform($value);

        if ($validator !== null && !$validator($value)) {
            throw new InvalidArgumentException(
                sprintf('Environment variable "%s" validation failed. Got: %s', $key, var_export($value, true))
            );
        }
        return $value;
    }
}
