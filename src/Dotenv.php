<?php

declare(strict_types=1);


namespace Enjoys\Dotenv;

use Enjoys\Dotenv\Parser\Parser;
use Enjoys\Dotenv\Parser\ParserInterface;

class Dotenv
{

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

    private bool $castType = false;

    public function __construct(
        private string $envFilePath,
        ?StorageInterface $storage = null,
        ?ParserInterface $parser = null
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
        $quoted = 0;
        if ($value !== null) {
            $value = preg_replace_callback('/^([\'"])?(.*)(\1)/', function ($matches) {
                return match ($matches[1]) {
                    "'" => $matches[2],
                    "\"" => strtr($matches[2], self::CHARACTER_MAP),
                    default => $matches[0],
                };
            }, $value, count: $quoted);
        }

        $value = $this->getVariablesResolver()->resolve($key, $value);


        if (getenv($key)) {
            $value = getenv($key);
        }

        return ($this->isCastType() && $quoted === 0) ? Helper::castType($value) : $value;
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
                if (!empty($key)) {
                    putenv($key); //unset
                }
            }
        }

        foreach (explode(',', (string)($_ENV['ENJOYS_DOTENV'] ?? '')) as $key) {
            unset($_ENV[$key]);
        }
    }


}
