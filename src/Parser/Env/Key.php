<?php

declare(strict_types=1);


namespace Enjoys\Dotenv\Parser\Env;


use Enjoys\Dotenv\Exception\InvalidArgumentException;

final class Key implements \Stringable
{
    private string $value;

    public function __construct(string $value)
    {
        if (!\preg_match('/^([A-Z_0-9]+)$/i', $value)) {
            throw new InvalidArgumentException(
                'The key %s have invalid chars. The key must have only letters (A-Z) digits (0-9) and _'
            );
        }
        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
