<?php

declare(strict_types=1);


namespace Enjoys\Dotenv;

use Enjoys\Dotenv\Parser\Parser;
use Enjoys\Dotenv\Parser\ParserInterface;

class Dotenv
{
    private string $baseDirectory;
    /**
     * @var array<string, string|bool|int|float|null>
     */
    private array $envRawArray = [];
    /**
     * @var array<string, string|bool|int|float|null>
     */
    private array $envArray = [];
    private ParserInterface $parser;


    public function __construct(
        string $baseDirectory,
        private string $envFilename = '.env',
        private string $distEnvFilename = '.env.dist',
        ?ParserInterface $parser = null
    ) {
        $this->baseDirectory = rtrim($baseDirectory, "/") . DIRECTORY_SEPARATOR;
        $this->parser = $parser ?? new Parser();
    }

    public function loadEnv(bool $usePutEnv = false): void
    {
        $this->doMerge($this->getGeneralPaths());
        $this->doMerge($this->getExtraPaths());
        $this->doLoad($usePutEnv);
    }

    /**
     * @return string[]
     */
    private function getExtraPaths(): array
    {
        $env = (getenv('APP_ENV') ?: null) ?? $this->envRawArray['APP_ENV'] ?? null;

        if ($env === '' || $env === null) {
            return [];
        }
        $path = realpath($this->baseDirectory . $this->envFilename . '.' . $env);

        if ($path === false) {
            return [];
        }

        return [$path];
    }

    /**
     * @return string[]
     */
    private function getGeneralPaths(): array
    {
        $paths = [
            realpath($this->baseDirectory . $this->distEnvFilename),
            realpath($this->baseDirectory . $this->envFilename)
        ];

        return array_filter($paths, function ($item) {
            return is_string($item);
        });
    }

    /**
     * @param string[] $array
     */
    private function doMerge(array $array): void
    {
        foreach ($array as $path) {
            $this->parser->parse(file_get_contents($path));
            $this->envRawArray = array_merge($this->envRawArray, $this->parser->getEnvArray());
        }
    }

    private function doLoad(bool $usePutEnv): void
    {
        foreach ($this->envRawArray as $key => $value) {
            if (getenv($key)) {
                $value = getenv($key);
            } else {
                if (gettype($value) === 'string') {
                    $value = stripslashes(ValuesHandler::handleVariables($key, $value, $this));
                }
            }

            $_ENV[$key] = ValuesHandler::cast($value);

            if (!getenv($key) && $usePutEnv === true) {
                putenv(sprintf("%s=%s", $key, ValuesHandler::scalarToString($value)));
            }

            $this->envArray[$key] = $_ENV[$key];
        }

        $_ENV['ENJOYS_DOTENV'] = implode(',', array_keys($this->envArray));
        putenv(sprintf('ENJOYS_DOTENV=%s', $_ENV['ENJOYS_DOTENV']));
    }

    private function isComment(string $line): bool
    {
        return str_starts_with($line, '#');
    }


    public function getEnvRawArray(): array
    {
        return $this->envRawArray;
    }

    public function getEnvArray(): array
    {
        return $this->envArray;
    }

    public static function clear(): void
    {
        if(false !== $envs = getenv('ENJOYS_DOTENV')){
            foreach (explode(',', $envs) as $key) {
                putenv($key); //unset
            }
        }

        foreach (explode(',', $_ENV['ENJOYS_DOTENV'] ?? '') as $key) {
            unset($_ENV[$key]);
        }

    }
}
