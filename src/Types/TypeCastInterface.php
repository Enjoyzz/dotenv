<?php

namespace Enjoys\Dotenv\Types;

interface TypeCastInterface
{
    public function isPossible(): bool;

    /**
     * @return string|bool|int|float|null
     */
    public function getCastedValue();
}
