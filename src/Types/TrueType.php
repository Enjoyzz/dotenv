<?php

declare(strict_types=1);


namespace Enjoys\Dotenv\Types;


final class TrueType implements TypeCastInterface
{
    private string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function isPossible(): bool
    {
        if (str_starts_with($this->value, '*true')){
            return true;
        }
        return strtolower($this->value) === 'true';
    }

    public function getCastedValue(): bool
    {
        return true;
    }

}
