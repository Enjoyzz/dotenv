<?php

declare(strict_types=1);


namespace Enjoys\Dotenv;


use Enjoys\Dotenv\Exception\InvalidArgumentException;

final class Variables
{
    public function __construct(private Dotenv $dotenv)
    {
    }

    public static function scalarValueToString(string|bool|int|float|null $value): string
    {
        if (gettype($value) === 'boolean') {
            return $value ? 'true' : 'false';
        }
        return (string)$value;
    }

    public function resolve(string $key, ?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $result = preg_replace_callback(
            '/(\${(?<variable>.+?)(?<default_value>:[-=?][^}]*)?})/',
            function (array $matches): string {
                $env = getenv($matches['variable']) !== false ? getenv($matches['variable']) : null;

                /** @var string|bool|int|float|null $val */
                $val =
                    $env ??
                    $this->dotenv->getEnvCollection()->get($matches['variable']) ??
                    $this->dotenv->getEnvRawArray()[$matches['variable']] ??
                    (($matches['default_value'] ?? null) !== null
                        ? $this->resolveDefaultValue(
                            $matches['default_value'],
                            $matches['variable']
                        ) : null)
                    ?? '';

                return self::scalarValueToString($val);
            },
            $value,
            flags: PREG_UNMATCHED_AS_NULL
        );

        if (preg_match('/(\${(.+?)})/', $result ?? '')) {
            return $this->resolve($key, $result);
        }

        return $result;
    }

    private function resolveDefaultValue(string $default_value, string $variable): string
    {
        $value = substr($default_value, 2);

        if ('?' === $default_value[1]) {
            throw new InvalidArgumentException(sprintf('Not set variable ${%s}. %s', $variable, $value));
        }

        if ('=' === $default_value[1]) {
            $this->dotenv->populate(
                $variable,
                $value,
            );
        }
        return $value;
    }
}
