<?php

declare(strict_types=1);


namespace Enjoys\Dotenv\Parser\Env;


use Enjoys\Dotenv\Exception\InvalidArgumentException;

final class Key implements \Stringable
{
    private string $value;

    public function __construct(string $value)
    {
        if (!\preg_match('/^([A-Z_])([A-Z_0-9]+)?$/', $value)) {
            throw new InvalidArgumentException(
                'The key %s have invalid chars.
                The key must be UPPERCASE and have only letters (A-Z) digits (0-9)  and _.
                And starts with A-Z or _'
            );
        }
        $this->value = $value;
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->value;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
