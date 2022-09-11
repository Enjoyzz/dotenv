<?php

declare(strict_types=1);


namespace Enjoys\Dotenv\Parser\Lines;


final class EmptyLine implements LineInterface
{
    public function __toString(): string
    {
        return '';
    }
}
