<?php

declare(strict_types=1);


namespace Enjoys\Dotenv\Parser;


use Enjoys\Dotenv\Parser\Env\Comment;
use Enjoys\Dotenv\Parser\Env\Key;
use Enjoys\Dotenv\Parser\Env\Value;
use Enjoys\Dotenv\Parser\Lines\CommentLine;
use Enjoys\Dotenv\Parser\Lines\EmptyLine;
use Enjoys\Dotenv\Parser\Lines\EnvLine;
use Enjoys\Dotenv\Parser\Lines\LineInterface;

final class Parser implements ParserInterface
{
    const AUTO_CAST_VALUE_TYPE = 1;
    /**
     * @var string[]
     */
    private array $rawLinesArray = [];

    /**
     * @var array<array-key, LineInterface>
     */
    private array $lines = [];

    public function __construct(private int $flags = 0)
    {
    }

    public function getRawLinesArray(): array
    {
        return $this->rawLinesArray;
    }

    private function clear(): void
    {
        unset($this->rawLinesArray, $this->lines);
    }

    public function parse(string $content): void
    {
        $this->clear();

        $this->rawLinesArray = array_map(
            'trim',
            preg_split("/\R/", $content)
        );

        foreach ($this->rawLinesArray as $rawLine) {
            if (empty($rawLine)) {
                $this->lines[] = new EmptyLine();
                continue;
            }

            if (str_starts_with($rawLine, '#')) {
                $this->lines[] = new CommentLine($rawLine);
                continue;
            }

            /** @var Key $key */
            /** @var Value|null $value */
            /** @var Comment|null $comment */
            [$key, $value, $comment] = $this->parseEnvLine($rawLine);
            $this->lines[$key->getValue()] = new EnvLine(
                $key,
                $value,
                $comment
            );
        }
    }

    /**
     * @return array<array-key, LineInterface>
     */
    public function getLines(): array
    {
        return $this->lines;
    }

    /**
     * @return array<array-key, EnvLine&LineInterface>
     * @psalm-suppress MoreSpecificReturnType, LessSpecificReturnStatement
     */
    public function getEnvLines(): array
    {
        return array_filter($this->lines, function ($item) {
            if ($item instanceof EnvLine) {
                return true;
            }
            return false;
        });
    }

    public function getEnvArray(): array
    {
        $envLines = $this->getEnvLines();
        $envArray = [];
        foreach ($envLines as $envLine) {
            $envArray[(string)$envLine->getKey()] = $envLine->getValue()?->getValue();
        }
        return $envArray;
    }

    /**
     * @param string $rawLine
     * @return array
     */
    private function parseEnvLine(string $rawLine): array
    {
        /**
         * $explodedLine[0] - rawKey
         * $explodedLine[1] - rawValue
         * @var string[] $explodedLine
         */
        $explodedLine = array_map('trim', explode('=', $rawLine, 2));

        return [
            new Key($explodedLine[0]),
            ...$this->parseValue($explodedLine[1] ??= null)
        ];
    }

    /**
     * @param string|null $rawValue
     * @return array<int, Value|Comment|null>
     */
    private function parseValue(?string $rawValue): array
    {
        if ($rawValue === null) {
            return [
                null,
                null
            ];
        }

        preg_match(
            '/^([\'"])(?<value>(?:(?!\1|\\\\).|\\\\.)*)\1(?<comment>.*)?/',
            $rawValue,
            $matches,
            PREG_UNMATCHED_AS_NULL
        );

        $matches['value'] ??= false;
        $matches['comment'] ??= null;

        if ($matches['value']) {
            return [
                new Value($matches['value'], true, $this->isAutoCastType()),
                $matches['comment'] ? new Comment($matches['comment']) : null
            ];
        }

        $unquotedValue = array_map('trim', explode('#', $rawValue, 2));
        return [
            new Value($unquotedValue[0], false, $this->isAutoCastType()),
            ($unquotedValue[1] ?? null) ? new Comment($unquotedValue[1]) : null
        ];
    }


    public function isAutoCastType(): bool
    {
        return ($this->flags & self::AUTO_CAST_VALUE_TYPE) === self::AUTO_CAST_VALUE_TYPE;
    }


}
