<?php

declare(strict_types=1);


namespace Enjoys\Dotenv;

use Enjoys\Dotenv\Parser\Parser;
use Enjoys\Dotenv\Parser\ParserInterface;

final class Dotenv
{

    public const CLEAR_MEMORY_AFTER_LOAD_ENV = 1;
    public const CAST_TYPE_ENV_VALUE = 2;
    public const POPULATE_PUTENV = 4;
    public const POPULATE_SERVER = 8;

    private const CHARACTER_MAP = [
        "\\n" => "\n",
        "\\\"" => "\"",
        "\\\\" => "\\",
        '\\\'' => "'",
        '\\t' => "\t"
    ];

    /**
     * @var array<string, string|null>
     */
    private array $envRawArray = [];

    private EnvCollection $envCollection;
    private ParserInterface $parser;
    private Variables $variablesResolver;
    private StorageInterface $storage;

    public function __construct(
        private string $envFilePath,
        ?StorageInterface $storage = null,
        ?ParserInterface $parser = null,
        private int $flags = 0
    ) {
        $this->envCollection = new EnvCollection();
        $this->parser = $parser ?? new Parser();
        $this->storage = $storage ?? new Storage();
        $this->variablesResolver = new Variables($this);
    }

    public function loadEnv(bool $usePutEnv = false): void
    {
        if ($usePutEnv){
            $this->flags = $this->flags | self::POPULATE_PUTENV;
        }

        $this->readFiles();
        $this->writeEnvs();


        putenv(sprintf('ENJOYS_DOTENV=%s', implode(',', $this->envCollection->getKeys())));

        if ($this->isClearMemory()) {
            $this->clearMemory();
        }
    }

    private function readFiles(): void
    {
        $this->storage->addPath($this->envFilePath . '.dist');
        $this->storage->addPath($this->envFilePath);

        while (false !== $path = $this->storage->getPath()) {
            if ($this->storage->isLoaded($path)) {
                continue;
            }

            $this->envRawArray = array_merge($this->envRawArray, $this->parser->parseEnv(file_get_contents($path)));
            $this->storage->markLoaded($path);
            $this->storage->addPath(
                $this->envFilePath . '.' . ((getenv('APP_ENV') ?: null) ?? $this->envRawArray['APP_ENV'] ?? '')
            );
        }
    }

    private function writeEnvs(): void
    {
        foreach ($this->envRawArray as $key => $value) {
            $this->populate($key, $value);
        }
    }

    public function handleValue(string $key, ?string $value): float|bool|int|string|null
    {

        if ($value !== null) {
            $quoted = 0;
            $value = preg_replace_callback('/^(?<quote>[\'"])?(?<value>.*)\1/', function ($matches) {
                return match ($matches['quote']) {
                    "'" => $matches['value'],
                    "\"" => strtr($matches['value'], self::CHARACTER_MAP)
                };
            }, $value, count: $quoted);

        }

        $value = $this->variablesResolver->resolve($key, $value);


        if (getenv($key)) {
            $value = getenv($key);
        }

        return ($this->isCastType() && ($quoted ?? null) === 0) ? ValueTypeCasting::castType($value) : $value;
    }

    public function populate(
        string $key,
        string|null $value,
    ): void {
        $value = $this->handleValue($key, $value);

        $_ENV[$key] = $value;
        $this->envCollection->add($key, $value);


        if (!getenv($key) && $this->isUsePutEnv() === true) {
            putenv(sprintf("%s=%s", $key, Variables::scalarValueToString($value)));
        }

        if ($this->isPopulateToServer()) {
            $_SERVER[$key] = $value;
        }

    }

    public function getEnvRawArray(): array
    {
        return $this->envRawArray;
    }

    public function getEnvCollection(): EnvCollection
    {
        return $this->envCollection;
    }

    /**
     * @return string[]
     */
    public function getLoadedPaths(): array
    {
        return $this->storage->getLoadedPaths();
    }


    public static function clear(): void
    {
        if (false !== $envs = getenv('ENJOYS_DOTENV')) {
            foreach (explode(',', $envs) as $key) {
                if (!empty($key)) {
                    //unset
                    putenv($key);
                    unset($_ENV[$key], $_SERVER[$key]);
                }
            }
        }
        putenv('ENJOYS_DOTENV');
    }

    public function enableCastType(): void
    {
        $this->flags = $this->flags | self::CAST_TYPE_ENV_VALUE;
    }

    public function disableCastType(): void
    {
        $this->flags = $this->flags ^ self::CAST_TYPE_ENV_VALUE;
    }

    private function clearMemory(): void
    {
        unset($this->envCollection, $this->variablesResolver);
    }

    private function isUsePutEnv(): bool
    {
        return ($this->flags & self::POPULATE_PUTENV) === self::POPULATE_PUTENV;
    }

    private function isPopulateToServer(): bool
    {
        return ($this->flags & self::POPULATE_SERVER) === self::POPULATE_SERVER;
    }

    private function isCastType(): bool
    {
        return ($this->flags & self::CAST_TYPE_ENV_VALUE) === self::CAST_TYPE_ENV_VALUE;
    }

    private function isClearMemory(): bool
    {
        return ($this->flags & self::CLEAR_MEMORY_AFTER_LOAD_ENV) === self::CLEAR_MEMORY_AFTER_LOAD_ENV;
    }

}
