<?php

declare(strict_types=1);


namespace Enjoys\Dotenv;


final class Helper
{
    /**
     * @param string|bool|int|float|null  $value
     * @return string
     */
    public static function scalarValueToString($value): string
    {
        if (gettype($value) === 'boolean') {
            return $value ? 'true' : 'false';
        }
        return (string)$value;
    }

    /**
     * @param string|bool|int|float|null  $value
     * @return string|bool|int|float|null
     */
    public static function castType($value)
    {
        if (gettype($value) !== 'string') {
            return $value;
        }
        return (new ValueTypecasting($value))->getCastValue();
    }
}
