<?php

declare(strict_types=1);


namespace Enjoys\Dotenv;

use Enjoys\Dotenv\Parser\Parser;
use Enjoys\Dotenv\Parser\ParserInterface;

class Dotenv
{
    private string $baseDirectory;
    /**
     * @var string[]
     */
    private array $envRawArray = [];
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
        /** @var string $key */
        foreach ($this->envRawArray as $key => $value) {
            if (gettype($value) === 'string'){
                $value = ValuesHandler::handleVariables($key, $value, $this);
                $value = stripslashes($value);
            }


            if (getenv($key)) {
                $value = getenv($key);
            }

            /** @var string $value */
            $_ENV[$key] = ValuesHandler::cast($value);

            if (!getenv($key) && $usePutEnv === true) {
                putenv("$key=$value");
            }

            $this->envArray[$key] = $_ENV[$key];
        }
    }

    public function setAutoCastType(bool $autoCastType): void
    {
        $this->autoCastType = $autoCastType;
    }

    private function isComment(string $line): bool
    {
        return str_starts_with($line, '#');
    }

    /**
     * @return string[]
     */
    public function getEnvRawArray(): array
    {
        return $this->envRawArray;
    }

    public function getEnvArray(): array
    {
        return $this->envArray;
    }

}
