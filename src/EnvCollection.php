<?php

declare(strict_types=1);


namespace Enjoys\Dotenv;


final class EnvCollection
{

    /**
     * @var array<string, mixed>
     */
    private array $collection = [];

    public function add(string $key, mixed $value): void
    {
        $this->collection[$key] = $value;
    }

    public function get(string $key, mixed $default = null): mixed
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
