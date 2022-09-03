<?php

declare(strict_types=1);

namespace Enjoys\Dotenv\Parser\Env;

use Enjoys\Dotenv\Parser\TypeDeterminant;
use Enjoys\Dotenv\ValuesHandler;

final class Value implements \Stringable
{
    private string|bool|int|float $value;

    /**
     * @var string[]
     */
    private static array $characterMap = array(
        "\\n" => "\n",
        "\\\"" => "\"",
        "\\\\" => "\\",
        '\\\'' => "'",
        '\\t' => "\t"
    );

    public function __construct(string $value, private bool $needQuotes = false, private string|false|null $quote = null, private bool $autoCastType = false)
    {
        $this->value = $this->handleValue($value);
    }

    private function handleValue(string $value): string|bool|int|float
    {
        if (preg_match('/[#]/', $value)) {
            $this->needQuotes = true;
        }

        if ($this->autoCastType && $this->needQuotes === false) {
            $determinant = new TypeDeterminant($value);
            $value = $determinant->getCastValue();
        }
        return $value;
    }

    public function __toString(): string
    {
        return $this->needQuotes ? sprintf(
            '"%s"',
            ValuesHandler::scalarToString($this->value)
        ) : ValuesHandler::scalarToString($this->value);
    }

    public function getValue(): string|bool|int|float
    {
        return $this->needQuotes && $this->quote === '"' ? strtr(
            ValuesHandler::scalarToString($this->value),
            self::$characterMap
        ) : $this->value;
    }


}
