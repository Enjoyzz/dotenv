<?php

declare(strict_types=1);


namespace Enjoys\Dotenv\Types;


final class BoolType implements TypeCastInterface
{
    public function __construct(private string $value)
    {
    }

    #[\Override]
    public function isPossible(): bool
    {
        if (str_starts_with($this->value, '*bool')){
            $this->value = preg_replace('/^(\*bool\s*)/', '', $this->value) ?? '';
            return true;
        }
        return false;
    }

    #[\Override]
    public function getCastedValue(): bool
    {
        return !empty($this->value);
    }
}
