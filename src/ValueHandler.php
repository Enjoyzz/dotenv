<?php

declare(strict_types=1);


namespace Enjoys\Dotenv;


use Webmozart\Assert\Assert;

final class ValueHandler
{


    /**
     * @var string[]
     */
    private array $envArray;

    /**
     * @param string[] $envArray
     */
    public function __construct(array $envArray)
    {
        $this->envArray = $envArray;
    }


    /**
     * @return string|bool|null|int|float
     */
    public function getHandledValue(string $value)
    {
        $value = $this->handleQuotes($value);
        $value = $this->handleVariables($value);
        return $this->castValues($value);
    }

    private function handleQuotes(string $value): string
    {
        return preg_replace('/^\"(.+)\"$/', '\\1', $value);
    }


    private function handleVariables(string $value): string
    {
        return preg_replace_callback(
            '/(\${(.+?)})/',
            function (array $matches) {
                /** @var string[] $matches */
                return $this->envArray[$matches[2]] ?? '';
            },
            $value
        );
    }


    /**
     * @param string $value
     * @return bool|float|int|string|null
     */
    private function castValues(string $value)
    {
        preg_match('/^\*(\w+)[\s+]?(.+)?/', $value, $match);
        switch ($match[1]) {
            case 'true':
                return true;
            case 'false':
                return false;
            case 'null':
                return null;
            case 'int':
                Assert::notEmpty($match[2], 'Invalid parameter for *int type, the value must not be empty');
                return (int)$match[2];
            case 'int8':
                Assert::notEmpty($match[2], 'Invalid parameter for *int type, the value must not be empty');
                return octdec($match[2]);
            case 'int16':
                Assert::notEmpty($match[2], 'Invalid parameter for *int type, the value must not be empty');
                return hexdec($match[2]);
            case 'float':
                Assert::notEmpty($match[2], 'Invalid parameter for *float type, the value must not be empty');
                return (float)str_replace(',', '.', $match[2]);
            case 'string':
                Assert::notEmpty($match[2], 'Invalid parameter for *string type, the value must not be empty');
                return $match[2];
            default:
                return $value;
        }
    }
}