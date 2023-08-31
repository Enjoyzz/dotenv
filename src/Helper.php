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

}
