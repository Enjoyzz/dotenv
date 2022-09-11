<?php

namespace Enjoys\Dotenv;

interface StorageInterface
{
    public function getPath(): string|false;

    public function isLoaded(string $path): bool;

    public function markLoaded(string $path): void;

    public function addPath(string $path): void;

    /**
     * @return string[]
     */
    public function getLoadedPaths(): array;
}
