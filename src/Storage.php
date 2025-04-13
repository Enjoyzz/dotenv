<?php

declare(strict_types=1);


namespace Enjoys\Dotenv;


final class Storage implements StorageInterface
{
    /**
     * @var string[]
     */
    private array $loadedPaths = [];
    /**
     * @var string[]
     */
    private array $paths = [];

    public function getPath(): string|false
    {
        $key = key($this->paths);
        if ($key === null) {
            return false;
        }
        $result = $this->paths[$key];
        unset($this->paths[$key]);
        return $result;
    }

    public function isLoaded(string $path): bool
    {
        return in_array($path, $this->loadedPaths, true);
    }

    public function markLoaded(string $path): void
    {
        $this->loadedPaths[] = $path;
    }

    public function addPath(string $path): void
    {
        $path = realpath($path);
        if ($path !== false) {
            $this->paths[] = $path;
        }
    }

    /**
     * @return string[]
     */
    public function getLoadedPaths(): array
    {
        return $this->loadedPaths;
    }
}
