<?php

declare(strict_types=1);


namespace Enjoys\Dotenv\Types;


final class FloatType implements TypeCastInterface
{
    public function __construct(private string $value)
    {
    }

    #[\Override]
    public function isPossible(): bool
    {
        $this->value = str_replace(',', '.', preg_replace('/^(\*float\s*|\*double\s*)/', '', $this->value) ?? '');

        if (is_numeric($this->value)) {
            return (string)(float)$this->value === $this->value;
        }
        return false;
    }

    #[\Override]
    public function getCastedValue(): float
    {
        return (float)$this->value;
    }
}
