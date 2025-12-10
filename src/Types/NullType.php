<?php

declare(strict_types=1);


namespace Enjoys\Dotenv\Types;


final class NullType implements TypeCastInterface
{
    public function __construct(private string $value)
    {

    }

    #[\Override]
    public function isPossible(): bool
    {
        if (str_starts_with($this->value, '*null')){
            return true;
        }
        return strtolower($this->value) === 'null';
    }

    #[\Override]
    public function getCastedValue(): string|null
    {
        return null;
    }
}
