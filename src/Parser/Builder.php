<?php

declare(strict_types=1);


namespace Enjoys\Dotenv\Parser;


use Enjoys\Dotenv\Parser\Lines\LineInterface;

final class Builder
{
    /**
     * @var LineInterface[]
     */
    private array $lines;

    /**
     * @param LineInterface[] $lines
     */
    public function __construct(array $lines)
    {
        $this->lines = $lines;
    }

    public function build(): string
    {
        return implode("\n", $this->lines);
    }
}
