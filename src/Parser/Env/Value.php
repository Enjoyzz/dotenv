<?php

declare(strict_types=1);

namespace Enjoys\Dotenv\Parser\Env;

final class Value implements \Stringable
{
    private string $value;

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


    public function __construct(
        string $value,
        private bool $needQuotes = false,
        private string|null $quote = null
    ) {
        $this->value = $this->handleValue($value);
    }

    private function handleValue(string $value): string
    {
        if (preg_match('/[#]/', $value)) {
            $this->needQuotes = true;
        }
        return $value;
    }

    public function __toString(): string
    {
        return $this->needQuotes ? sprintf('"%s"', $this->value) : $this->value;
    }

    public function getValue(): string
    {
        return ($this->needQuotes && $this->quote === '"') ? strtr($this->value, self::$characterMap) : $this->value;
    }


    public function getQuote(): ?string
    {
        return $this->quote;
    }

}
