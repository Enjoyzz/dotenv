<?php

declare(strict_types=1);


namespace Enjoys\Dotenv\Parser\Env;


use Enjoys\Dotenv\Parser\TypeDeterminant;

final class Value implements \Stringable
{
    private string|bool|int|float $value;

    public function __construct(string $value, private bool $needQuotes = false, private bool $autoCastType = false)
    {
        $this->value = $this->handleValue($value);
    }

    private function handleValue(string $value): string|bool|int|float
    {
        if (preg_match('/[#]/', $value)){
            $this->needQuotes = true;
        }

        if ($this->autoCastType && $this->needQuotes === false){
            $determinant = new TypeDeterminant($value);
            $value = $determinant->getCastValue();
        }
        return $value;
    }

    public function __toString(): string
    {
        return $this->needQuotes ? sprintf('"%s"', (string)$this->value) : (string)$this->value;
    }

    public function getValue(): string|bool|int|float
    {
        return $this->value;
    }


}
