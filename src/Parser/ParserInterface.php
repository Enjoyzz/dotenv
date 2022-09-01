<?php

declare(strict_types=1);


namespace Enjoys\Dotenv\Parser;


interface ParserInterface
{
    public function parse(string $content): void;
    public function getEnvArray(): array;
}
