<?php

declare(strict_types=1);


namespace Enjoys\Dotenv\Parser;


interface ParserInterface
{
    public function parse(string $content): void;

    /**
     * @return array<string, string|bool|int|float|null>
     */
    public function getEnvArray(): array;
}
