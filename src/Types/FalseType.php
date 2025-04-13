<?php

declare(strict_types=1);


namespace Enjoys\Dotenv\Types;


final class FalseType implements TypeCastInterface
{
    public function __construct(private string $value)
    {

    }

    #[\Override]
    public function isPossible(): bool
    {
        if (str_starts_with($this->value, '*false')){
            return true;
        }
        return strtolower($this->value) === 'false';
    }

    #[\Override]
    public function getCastedValue(): bool
    {
        return false;
    }
}
