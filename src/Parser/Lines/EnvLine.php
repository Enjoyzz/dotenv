<?php

declare(strict_types=1);


namespace Enjoys\Dotenv\Parser\Lines;


use Enjoys\Dotenv\Parser\Env\Comment;
use Enjoys\Dotenv\Parser\Env\Key;
use Enjoys\Dotenv\Parser\Env\Value;

final class EnvLine implements LineInterface
{
    private Key $key;
    private ?Value $value;
    private ?Comment $comment;

    public function __construct(Key $key, ?Value $value = null, ?Comment $comment = null)
    {
        $this->key = $key;
        $this->value = $value;
        $this->comment = $comment;
    }

    public function __toString(): string
    {
        $format = ($this->value === null) ? '%s%s%s' : '%s=%s%s';
        /** @psalm-suppress  ImplicitToStringCast, PossiblyNullArgument */
        return sprintf($format, $this->key, $this->value, $this->comment);
    }

    public function getKey(): Key
    {
        return $this->key;
    }

    public function getValue(): ?Value
    {
        return $this->value;
    }

    public function getComment(): ?Comment
    {
        return $this->comment;
    }
}
