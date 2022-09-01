<?php

declare(strict_types=1);


namespace Enjoys\Dotenv\Parser;


interface ParserInterface
{
    public function parse(): void;
    public function getEnvArray(): array;
}
