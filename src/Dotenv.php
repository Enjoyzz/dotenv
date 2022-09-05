<?php

declare(strict_types=1);


namespace Enjoys\Dotenv;

use Enjoys\Dotenv\Parser\Parser;
use Enjoys\Dotenv\Parser\ParserInterface;

class Dotenv
{

    /**
     * @var array<string, string|null>
     */
    private array $envRawArray = [];
    /**
     * @var array<string, string|null>
     */
    private array $envQuotesMap = [];
    /**
     * @var array<string, string|bool|int|float|null>
     */
    private array $envArray = [];


    private ParserInterface $parser;
    private Variables $variablesResolver;

    private bool $castType = false;
    private StorageInterface $storage;


    public function __construct(
        private string $envFilePath,
        ?StorageInterface $storage = null,
        ?ParserInterface $parser = null
    ) {
        $this->parser = $parser ?? new Parser();
        $this->storage = $storage ?? new Storage();
        $this->variablesResolver = new Variables($this);
    }

    public function loadEnv(bool $usePutEnv = false): void
    {
        $this->readFiles();
        $this->writeEnvs($usePutEnv);

        $_ENV['ENJOYS_DOTENV'] = implode(',', array_keys($this->envArray));
        putenv(sprintf('ENJOYS_DOTENV=%s', $_ENV['ENJOYS_DOTENV']));
    }

    private function readFiles(): void
    {
        $this->storage->addPath($this->envFilePath . '.dist');
        $this->storage->addPath($this->envFilePath);

        while (false !== $path = $this->storage->getPath()) {
            if ($this->storage->isLoaded($path)) {
                continue;
            }
            $this->parser->parse(file_get_contents($path));
            $this->envRawArray = array_merge($this->envRawArray, $this->parser->getEnvArray());
            $this->envQuotesMap = array_merge($this->envQuotesMap, $this->parser->getEnvQuotesMap());
            $this->storage->markLoaded($path);
            $this->storage->addPath(
                $this->envFilePath . '.' . ((getenv('APP_ENV') ?: null) ?? $this->envRawArray['APP_ENV'] ?? '')
            );
        }

        $this->envQuotesMap = array_filter($this->envQuotesMap);
    }

    private function writeEnvs(bool $usePutEnv): void
    {
        foreach ($this->envRawArray as $key => $value) {
            self::writeEnv($key, $value, $this, $usePutEnv);
        }
    }

    public static function writeEnv(
        string $key,
        ?string $value,
        Dotenv $dotenv,
        bool $usePutEnv = false
    ): void {
        $value = $dotenv->getVariablesResolver()->resolve($key, $value);

        if (!getenv($key) && $usePutEnv === true) {
            putenv(sprintf("%s=%s", $key, $value ?? ''));
        }

        if (getenv($key)) {
            $value = getenv($key);
        }

        $_ENV[$key] = $dotenv->envArray[$key] = ($dotenv->isCastType() && !in_array($key, array_keys($dotenv->envQuotesMap), true) ? Helper::castType($value) : $value);
    }

    public function getEnvRawArray(): array
    {
        return $this->envRawArray;
    }

    public function getEnvArray(): array
    {
        return $this->envArray;
    }

    /**
     * @return string[]
     */
    public function getLoadedPaths(): array
    {
        return $this->storage->getLoadedPaths();
    }


    public function getVariablesResolver(): Variables
    {
        return $this->variablesResolver;
    }

    public function isCastType(): bool
    {
        return $this->castType;
    }

    public function setCastType(bool $castType): void
    {
        $this->castType = $castType;
    }

    public static function clear(): void
    {
        if (false !== $envs = getenv('ENJOYS_DOTENV')) {
            foreach (explode(',', $envs) as $key) {
                if (!empty($key)){
                    putenv($key); //unset
                }
            }
        }

        foreach (explode(',', (string)($_ENV['ENJOYS_DOTENV'] ?? '')) as $key) {
            unset($_ENV[$key]);
        }
    }
}
