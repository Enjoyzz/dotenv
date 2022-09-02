<?php

declare(strict_types=1);


namespace Enjoys\Dotenv\Parser;


use Enjoys\Dotenv\Parser\Lines\LineInterface;

final class Builder
{
    /**
     * @param LineInterface[] $lines
     */
    public function __construct(private array $lines)
    {
    }

    public function build(): string
    {
        return implode("\n", $this->lines);
    }
}
