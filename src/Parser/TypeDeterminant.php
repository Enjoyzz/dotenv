<?php

declare(strict_types=1);


namespace Enjoys\Dotenv\Parser;


final class TypeDeterminant
{

    private const DEFINABLE_TYPES_MAP = [
        'int',
        'float',
        'true',
        'false',
    ];

    private float|bool|int|string $castedValue;
    private string $possibleType = 'string';

    public function __construct(private string $originalValue)
    {
        $this->castedValue = $this->originalValue;
        $this->determine();
    }

    public function getCastValue(): float|bool|int|string
    {
        return $this->castedValue;
    }

    public function getPossibleType(): string
    {
        return $this->possibleType;
    }

    private function determine(): void
    {
        foreach (self::DEFINABLE_TYPES_MAP as $type) {
            $func = 'is' . ucfirst($type);
            if ($this->$func($this->originalValue)) {
                $this->setPossibleType($type);
                settype($this->castedValue, $this->possibleType);
                break;
            }
        }
    }

    public function setPossibleType(string $possibleType): void
    {
        if (in_array($possibleType, ['true', 'false'])) {
            $possibleType = 'bool';
        }
        $this->possibleType = $possibleType;
    }

    private function isInt(string $value): bool
    {
        if (is_numeric($value)) {
            return (string)(int)$value === $this->originalValue;
        }
        return false;
    }


    private function isFloat(string $value): bool
    {
        if (is_numeric($value)) {
            return (string)(float)$value === $this->originalValue;
        }
        return false;
    }

    private function isTrue(string $value): bool
    {
        if (strtolower($value) === 'true'){
            $this->castedValue = true;
            return true;
        }
        return false;
    }

    private function isFalse(string $value): bool
    {
        if (strtolower($value) === 'false'){
            $this->castedValue = false;
            return true;
        }
        return false;
    }

}
