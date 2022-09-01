<?php

declare(strict_types=1);


namespace Enjoys\Dotenv\Parser\Env;


final class Comment implements \Stringable
{
    public function __construct(private string $value)
    {
    }

    public function __toString(): string
    {
        return sprintf(' #%s', $this->value);
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
