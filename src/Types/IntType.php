<?php

declare(strict_types=1);


namespace Enjoys\Dotenv\Types;


final class IntType implements TypeCastInterface
{
    public function __construct(private string $value)
    {
    }

    public function isPossible(): bool
    {
        $this->value = preg_replace('/^(\*int\s*)/', '', $this->value);

        if (is_numeric($this->value)) {
            return (string)(int)$this->value === $this->value;
        }
        return false;
    }

    public function getCastedValue(): int
    {
        return (int) $this->value;
    }
}
