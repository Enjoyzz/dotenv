<?php

declare(strict_types=1);


namespace Enjoys\Dotenv\Parser\Env;


final class Comment implements \Stringable
{
    private string $value;

    public function __construct(string $value)
    {
        $this->value = ltrim(trim($value), "#");
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
