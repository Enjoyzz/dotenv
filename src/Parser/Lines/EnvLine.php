<?php

declare(strict_types=1);


namespace Enjoys\Dotenv\Parser\Lines;


use Enjoys\Dotenv\Parser\Env\Comment;
use Enjoys\Dotenv\Parser\Env\Key;
use Enjoys\Dotenv\Parser\Env\Value;

final class EnvLine
{
    public function __construct(private Key $key, private ?Value $value = null, private ?Comment $comment = null)
    {
    }

    public function __toString(): string
    {
        $format = '%s=%s%s';
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
