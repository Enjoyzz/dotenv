<?php

declare(strict_types=1);


namespace Enjoys\Dotenv;


use RuntimeException;
use Webmozart\Assert\Assert;

use function sprintf;

final class ValuesHandler
{


    public static function cast(string|bool|int|float|null $value): string|bool|int|float|null
    {
        if (gettype($value) !== 'string') {
            return $value;
        }

        preg_match('/^\*(\w+)[\s+]?(.+)?/', $value, $match);

        $v = $match[2] ?? '';

        switch ($match[1] ?? '') {
            case 'bool':
                if (!empty($v)) {
                    return true;
                }
                return false;
            case 'true':
                return true;
            case 'false':
                return false;
            case 'null':
                return null;
            case 'int':
                Assert::notEmpty($v, 'Invalid parameter for *int type, the value must not be empty');
                return (int)$v;
            case 'int8':
                Assert::notEmpty($v, 'Invalid parameter for *int type, the value must not be empty');
                return octdec($v);
            case 'int16':
                Assert::notEmpty($v, 'Invalid parameter for *int type, the value must not be empty');
                return hexdec($v);
            case 'float':
            case 'double':
                Assert::notEmpty($v, 'Invalid parameter for *float type, the value must not be empty');
                return (float)str_replace(',', '.', $v);
            case 'string':
                Assert::notEmpty($v, 'Invalid parameter for *string type, the value must not be empty');
                return $v;
            default:
                return $value;
        }
    }

    public static function handleVariables(string $key, ?string $value, Dotenv $dotenv): string
    {
        if ($value === null) {
            return '*null';
        }
        $result = preg_replace_callback(
            '/(\${(?<variable>.+?)})/',
            function (array $matches) use ($dotenv): string {
                $env = getenv($matches['variable']);

                /** @var string|bool|int|float|null $val */
                $val =
                    ($env ? addslashes($env) : null) ??
                    $dotenv->getEnvRawArray()[$matches['variable']] ??
                    throw new RuntimeException(sprintf('Not found variable ${%s}.', $matches['variable']));

                $type = match (get_debug_type($val)) {
                    'bool' => '*bool ',
                    'int' => '*int ',
                    'float' => '*float ',
                    'null' => '*null ',
                    default => ''
                };

                return $type . (string)$val;
            },
            $value
        );

        if (preg_match('/(\${(.+?)})/', $result)) {
            return self::handleVariables($key, $result, $dotenv);
        }

        return $result;
    }

    public static function scalarToString(string|bool|int|float|null $value): string
    {
        if (gettype($value) === 'boolean') {
            return $value ? 'true' : 'false';
        }
        return (string)$value;
    }
}
