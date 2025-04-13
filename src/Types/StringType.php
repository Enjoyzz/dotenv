<?php

declare(strict_types=1);


namespace Enjoys\Dotenv\Types;


final class StringType implements TypeCastInterface
{
    public function __construct(private string $value)
    {
    }

    public function isPossible(): bool
    {
        if (str_starts_with($this->value, '*string')){
            $this->value = preg_replace('/^(\*string\s*)/', '', $this->value) ?? '';
            return true;
        }
        return false;
    }

    public function getCastedValue(): string
    {
        return $this->value;
    }
}
