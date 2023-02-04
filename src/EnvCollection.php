<?php

declare(strict_types=1);


namespace Enjoys\Dotenv;


final class EnvCollection
{

    /**
     * @var array<string, string|bool|int|float|null>
     */
    private array $collection = [];

    /**
     * @param string $key
     * @param string|bool|int|float|null  $value
     * @return void
     */
    public function add(string $key, $value): void
    {
        $this->collection[$key] = $value;
    }

    /**
     * @param string $key
     * @param float|bool|int|string|null  $default
     * @return float|bool|int|string|null
     */
    public function get(string $key, $default = null)
    {
        return $this->has($key) ? $this->collection[$key] : $default;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->collection);
    }

    public function delete(string $key): void
    {
        unset($this->collection[$key]);
    }

    /**
     * @return string[]
     */
    public function getKeys(): array
    {
        return array_keys($this->collection);
    }


    public function getCollection(): array
    {
        return $this->collection;
    }


}
