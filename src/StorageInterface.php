<?php

namespace Enjoys\Dotenv;

interface StorageInterface
{
    /**
     * @return string|false
     */
    public function getPath();

    public function isLoaded(string $path): bool;

    public function markLoaded(string $path): void;

    public function addPath(string $path): void;

    /**
     * @return string[]
     */
    public function getLoadedPaths(): array;
}
