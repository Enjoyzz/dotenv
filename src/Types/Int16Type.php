<?php

declare(strict_types=1);


namespace Enjoys\Dotenv\Types;


final class Int16Type implements TypeCastInterface
{
    private string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function isPossible(): bool
    {
        if (str_starts_with($this->value, '*int16')){
            $this->value = preg_replace('/^(\*int16\s*)/', '', $this->value);
            return true;
        }
        return false;
    }

    /**
     * @return int|float
     */
    public function getCastedValue()
    {
        return hexdec($this->value);
    }
}
