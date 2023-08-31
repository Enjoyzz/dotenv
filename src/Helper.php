<?php

declare(strict_types=1);


namespace Enjoys\Dotenv;


final class Helper
{
    public static function scalarValueToString(string|bool|int|float|null $value): string
    {
        if (gettype($value) === 'boolean') {
            return $value ? 'true' : 'false';
        }
        return (string)$value;
    }

    public static function castType(string|bool|int|float|null $value): string|bool|int|float|null
    {
        if (gettype($value) !== 'string') {
            return $value;
        }
        return (new ValueTypeCasting($value))->getCastValue();
    }
}
