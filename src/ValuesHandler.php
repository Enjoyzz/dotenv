<?php

declare(strict_types=1);


namespace Enjoys\Dotenv;


use Webmozart\Assert\Assert;

final class ValuesHandler
{
    public static function quotes(string $value): string
    {
        return preg_replace('/^\"(.+)\"$/', '\\1', $value);
    }

    /**
     * @param string $value
     * @return bool|float|int|string|null
     */
    public static function cast(string $value)
    {
        preg_match('/^\*(\w+)[\s+]?(.+)?/', $value, $match);

        $v = $match[2] ?? '';

        switch ($match[1] ?? '') {
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
                Assert::notEmpty($v, 'Invalid parameter for *float type, the value must not be empty');
                return (float)str_replace(',', '.', $v);
            case 'string':
                Assert::notEmpty($v, 'Invalid parameter for *string type, the value must not be empty');
                return $v;
            default:
                return $value;
        }
    }

    /**
     * @
     */
    public static function handleVariables(string $key, string $value, Dotenv $dotenv): string
    {
        $result = preg_replace_callback(
            '/(\${(.+?)})/',
            function (array $matches) use ($dotenv) {
                Assert::keyExists($dotenv->getEnvArray(), $matches[2], \sprintf('Not found variable ${%s}.', $matches[2]));
                return $dotenv->getEnvArray()[$matches[2]];
            },
            $value
        );

        if (preg_match('/(\${(.+?)})/', $result)) {
            return self::handleVariables($key, $result, $dotenv);
        }

        return $result;
    }
}