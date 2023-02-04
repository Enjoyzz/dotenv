<?php

declare(strict_types=1);


namespace Enjoys\Dotenv\Types;


final class NullType implements TypeCastInterface
{
    private string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function isPossible(): bool
    {
        if (str_starts_with($this->value, '*null')){
            return true;
        }
        return false;
    }

    public function getCastedValue(): ?string
    {
        return null;
    }
}
