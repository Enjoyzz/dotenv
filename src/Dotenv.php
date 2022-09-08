<?php

declare(strict_types=1);


namespace Enjoys\Dotenv;

use Enjoys\Dotenv\Parser\Parser;
use Enjoys\Dotenv\Parser\ParserInterface;

class Dotenv
{

    public const CLEAR_MEMORY_AFTER_LOAD_ENV = 1;
    public const CAST_TYPE_ENV_VALUE = 2;

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
        $this->readFiles();
        $this->writeEnvs($usePutEnv);

        $_ENV['ENJOYS_DOTENV'] = implode(',', $this->envCollection->getKeys());
        putenv(sprintf('ENJOYS_DOTENV=%s', $_ENV['ENJOYS_DOTENV']));

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

    private function writeEnvs(bool $usePutEnv): void
    {
        foreach ($this->envRawArray as $key => $value) {
            self::writeEnv($key, $this->handleValue($key, $value), $this->envCollection, $usePutEnv);
        }
    }

    public function handleValue(string $key, ?string $value): float|bool|int|string|null
    {

        if ($value !== null) {
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

        return ($this->isCastType() && ($quoted ?? null) === 0) ? Helper::castType($value) : $value;
    }

    public static function writeEnv(
        string $key,
        string|bool|int|float|null $value,
        EnvCollection $envCollection,
        bool $usePutEnv = false
    ): void {
        if (!getenv($key) && $usePutEnv === true) {
            putenv(sprintf("%s=%s", $key, Helper::scalarValueToString($value)));
        }
        $_ENV[$key] = $value;
        $envCollection->add($key, $value);
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
                    putenv($key); //unset
                }
            }
        }

        foreach (explode(',', (string)($_ENV['ENJOYS_DOTENV'] ?? '')) as $key) {
            unset($_ENV[$key]);
        }
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

    private function isCastType(): bool
    {
        return ($this->flags & self::CAST_TYPE_ENV_VALUE) === self::CAST_TYPE_ENV_VALUE;
    }

    private function isClearMemory(): bool
    {
        return ($this->flags & self::CLEAR_MEMORY_AFTER_LOAD_ENV) === self::CLEAR_MEMORY_AFTER_LOAD_ENV;
    }

}
