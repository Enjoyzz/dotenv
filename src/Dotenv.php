<?php

declare(strict_types=1);


namespace Enjoys\Dotenv;

use Webmozart\Assert\Assert;

class Dotenv
{
    private string $baseDirectory;
    /**
     * @var string[]
     */
    private array $envRawArray = [];
    private array $envArray = [];

    public function __construct(
        string $baseDirectory,
        private string $envFilename = '.env',
        private string $distEnvFilename = '.env.dist'
    ) {
        $this->baseDirectory = rtrim($baseDirectory, "/") . DIRECTORY_SEPARATOR;
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
            $this->envRawArray = array_merge($this->envRawArray, $this->getArrayData($path));
        }
    }

    /**
     * @return string[]
     */
    private function getArrayData(string $path): array
    {
        $result = [];

        $data = file_get_contents($path);

        /**
         * @var string $key
         * @var string $value
         */
        foreach ($this->parseToArray($data) as $key => $value) {
            $result[$key] = $value;
        }
        return $result;
    }


    private function parseToArray(string $input): \Generator
    {
        foreach (preg_split("/\R/", $input) as $line) {
            $line = trim($line);
            if ($this->isComment($line)) {
                continue;
            }
            $fields = array_map('trim', explode('=', $line, 2));

            if (count($fields) == 2) {
                list($key, $value) = $fields;
                Assert::regex(
                    $key,
                    '/^([A-Z_0-9]+)$/i',
                    'The key %s have invalid chars. The key must have only letters (A-Z) digits (0-9) and _'
                );


                yield $key => $this->parseValue($value);
            }
        }
    }


    private function doLoad(bool $usePutEnv): void
    {
        /** @var string $key */
        foreach ($this->envRawArray as $key => $value) {
            $value = ValuesHandler::quotes($value);
            $value = ValuesHandler::handleVariables($key, $value, $this);

            $value = stripslashes($value);

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

    private function isComment(string $line): bool
    {
        return (bool)preg_match('/^#/', $line);
    }

    private function parseValue(string $value): string
    {
        preg_match('/^([\'"])((?<value>.*?)(?<!\\\\)\1)/', $value, $matches);
        if (isset($matches['value'])){
            return $matches['value'];
        }
        return array_map('trim', explode('#', $value, 2))[0];
    }

    /**
     * @return string[]
     */
    public function getEnvRawArray(): array
    {
        return $this->envRawArray;
    }

    /**
     * @return array
     */
    public function getEnvArray(): array
    {
        return $this->envArray;
    }

}