<?php

namespace Enjoys\Dotenv\Types;

interface TypeCastInterface
{
    public function isPossible(): bool;

    public function getCastedValue(): string|bool|int|float|null;
}
