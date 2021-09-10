<?php

declare(strict_types=1);


namespace Enjoys\Dotenv;


use Enjoys\Dotenv\Exception\InvalidParameter;

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
     * @throws InvalidParameter
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


    /**
     * @throws InvalidParameter
     */
    private function castValues(): void
    {
        preg_match('/^\*(\w+)(\s+)?(.+)?/', (string)$this->value, $match);
        switch ($match[1]) {
            case 'int':
                if (!isset($match[3])) {
                    throw new InvalidParameter(
                        sprintf('Invalid parameter for *%s type, the value must not be empty', $match[1])
                    );
                }
                $this->value = (int)$match[3];
                break;
            case 'true':
                $this->value = true;
                break;
            case 'false':
                $this->value = false;
                break;
            case 'null':
                $this->value = null;
                break;
            case 'float':
                if (!isset($match[3])) {
                    throw new InvalidParameter(
                        sprintf('Invalid parameter for *%s type, the value must not be empty', $match[1])
                    );
                }
                $this->value = (float)str_replace(',', '.', $match[3]);
                break;
        }
    }
}