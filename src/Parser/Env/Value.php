<?php

declare(strict_types=1);


namespace Enjoys\Dotenv\Parser\Env;


final class Value implements \Stringable
{
    public function __construct(private string $value, private bool $needQuotes = false)
    {
        if (preg_match('/[#]/', $this->value)){
            $this->needQuotes = true;
        }
    }

    public function __toString(): string
    {
        return $this->needQuotes ? sprintf('"%s"', $this->value) : $this->value;
    }

    public function getValue(): string
    {
        return $this->value;
    }


}
