<?php

declare(strict_types=1);

namespace Enjoys\Dotenv\Parser\Env;

final class Value implements \Stringable
{
    private string $value;
    private ?string $quote;


    public function __construct(
        string $value,
        ?string $quote = null
    ) {
        $this->quote = $quote;
        $this->value = $this->handleValue($value);
    }

    private function handleValue(string $value): string
    {
        if (preg_match('/#+/', $value)) {
            $this->quote = '"';
        }
        if (preg_match('/^["\']+/', $value)) {
            $this->quote = null;
        }
        return $this->quote ? sprintf('%2$s%1$s%2$s', $value, $this->quote) : $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

}
