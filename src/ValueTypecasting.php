<?php

declare(strict_types=1);


namespace Enjoys\Dotenv;


use Enjoys\Dotenv\Types\BoolType;
use Enjoys\Dotenv\Types\FalseType;
use Enjoys\Dotenv\Types\FloatType;
use Enjoys\Dotenv\Types\Int16Type;
use Enjoys\Dotenv\Types\Int8Type;
use Enjoys\Dotenv\Types\IntType;
use Enjoys\Dotenv\Types\NullType;
use Enjoys\Dotenv\Types\StringType;
use Enjoys\Dotenv\Types\TrueType;
use Enjoys\Dotenv\Types\TypeCastInterface;

final class ValueTypecasting
{

    private const DEFINABLE_TYPES_MAP = [
        IntType::class,
        FloatType::class,
        TrueType::class,
        FalseType::class,
        NullType::class,
        BoolType::class,
        StringType::class,
        Int8Type::class,
        Int16Type::class
    ];

    /**
     * @var float|bool|int|string|null
     */
    private $castedValue;
    private string $originalValue;

    public function __construct(string $originalValue)
    {
        $this->originalValue = $originalValue;
        $this->castedValue = $this->originalValue;
        $this->determine();

    }

    /**
     * @return float|bool|int|string|null
     */
    public function getCastValue()
    {
        return $this->castedValue;
    }

    private function determine(): void
    {
         foreach (self::DEFINABLE_TYPES_MAP as $typeClass) {
             /** @var TypeCastInterface $type */
             $type = new $typeClass($this->originalValue);
            if ($type->isPossible()){
                $this->castedValue = $type->getCastedValue();
                break;
            }
        }
    }



}
