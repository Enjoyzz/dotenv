<?php

declare(strict_types=1);


namespace Enjoys\Dotenv\Parser\Env;


use Enjoys\Dotenv\Parser\TypeDeterminant;

final class Value implements \Stringable
{
    private mixed $value;

    public function __construct(string $value, private bool $needQuotes = false, private bool $autoCastType = false)
    {
        $this->value = $value;
        $this->handleValue();
    }

    private function handleValue()
    {
        if (preg_match('/[#]/', $this->value)){
            $this->needQuotes = true;
        }

        if ($this->autoCastType){
            $determinant = new TypeDeterminant($this->value);
            $this->value = $determinant->getCastValue();
        }

    }

    public function __toString(): string
    {
        return $this->needQuotes ? sprintf('"%s"', (string)$this->value) : $this->value;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }


}
