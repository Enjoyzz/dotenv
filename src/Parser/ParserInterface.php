<?php

declare(strict_types=1);


namespace Enjoys\Dotenv\Parser;


use Enjoys\Dotenv\Parser\Lines\LineInterface;

interface ParserInterface
{
    /**
     * @param string $content
     * @return array<string, string|null>
     */
    public function parseEnv(string $content): array;
    /**
     * @param string $content
     * @return array<array-key, LineInterface>
     */
    public function parseStructure(string $content): array;
}
