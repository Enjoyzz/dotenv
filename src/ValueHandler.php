<?php

declare(strict_types=1);


namespace Enjoys\Dotenv;


use Webmozart\Assert\Assert;

final class ValueHandler
{

    /**
     * @var string|bool|null|int|float
     */
    private $value;

    /**
     * @var string[]
     */
    private array $envArray;

    /**
     * @param string $value
     * @param string[] $envArray
     */
    public function __construct(string $value, array $envArray)
    {
        $this->value = $value;
        $this->envArray = $envArray;
    }


    /**
     * @return string|bool|null|int|float
     */
    public function getHandledValue()
    {
        $this->handleQuotes();
        $this->handleVariables();
        $this->castValues();

        return $this->value;
    }

    private function handleQuotes(): void
    {
        $this->value = preg_replace_callback(
            '/^\"(.+)\"$/',
            function (array $matches) {
                return (string)$matches[1];
            },
            (string)$this->value
        );
    }


    private function handleVariables(): void
    {
        $this->value = preg_replace_callback(
            '/(\${(.+?)})/',
            function (array $matches) {
                return $this->envArray[(string)$matches[2]] ?? '';
            },
            (string)$this->value
        );
    }


    private function castValues(): void
    {
        preg_match('/^\*(\w+)(\s+)?(.+)?/', (string)$this->value, $match);
        switch ($match[1]) {
            case 'true':
                $this->value = true;
                break;
            case 'false':
                $this->value = false;
                break;
            case 'null':
                $this->value = null;
                break;
            case 'int':
                Assert::notEmpty($match[3], 'Invalid parameter for *int type, the value must not be empty');
                $this->value = (int)$match[3];
                break;
            case 'float':
                Assert::notEmpty($match[3], 'Invalid parameter for *float type, the value must not be empty');
                $this->value = (float)str_replace(',', '.', $match[3]);
                break;
            case 'string':
                Assert::notEmpty($match[3], 'Invalid parameter for *string type, the value must not be empty');
                $this->value = $match[3];
                break;
            default:
                if (is_numeric($this->value)) {
                    $this->value = $this->value + 0;
                }
                break;
        }
    }
}