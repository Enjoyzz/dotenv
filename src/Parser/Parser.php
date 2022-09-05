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
use Enjoys\Dotenv\Parser\Lines\Lines;

final class Parser implements ParserInterface
{

    /**
     * @var string[]
     */
    private array $rawLinesArray = [];

    /**
     * @var array<string, string|null>
     */
    private array $envArray = [];
    /**
     * @var array<string, string|null>
     */
    private array $envQuotesMap = [];

    /**
     * @var array<array-key, LineInterface>
     */
    private array $lines = [];

    public function getRawLinesArray(): array
    {
        return $this->rawLinesArray;
    }

    private function clear(): void
    {
        $this->rawLinesArray = [];
        $this->lines = [];
    }

    public function parse(string $content): void
    {
        $this->clear();

        $this->rawLinesArray = Lines::handle(
            array_map(
                'trim',
                preg_split("/\R/", $content)
            )
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

            [$key, $value, $comment] = $this->parseEnvLine($rawLine);
            $this->lines[$key->getValue()] = new EnvLine(
                $key,
                $value,
                $comment
            );
        }

        $envLines = $this->getEnvLines();
        foreach ($envLines as $envLine) {
            $this->envArray[(string)$envLine->getKey()] = $envLine->getValue()?->getValue();
            $this->envQuotesMap[(string)$envLine->getKey()] = $envLine->getValue()?->getQuote();
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

    /**
     * @return array<string, string|null>
     */
    public function getEnvArray(): array
    {
        return $this->envArray;
    }

    /**
     * @return array<string, string|null>
     */
    public function getEnvQuotesMap(): array
    {
        return $this->envQuotesMap;
    }

    /**
     * @param string $rawLine
     * @return array{0: Key, 1: Value|null, 2: Comment|null}
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
            '/^(?P<quote>[\'"])(?P<value>(?:(?!\1|\\\\).|\\\\.)*)\1(?P<comment>.*)?/',
            $rawValue,
            $matches,
            PREG_UNMATCHED_AS_NULL
        );

        $matches['value'] ??= false;
        $matches['comment'] ??= null;

        if ($matches['value']) {
            return [
                new Value($matches['value'], true, !$matches['quote'] ? null : $matches['quote']),
                $matches['comment'] ? new Comment($matches['comment']) : null
            ];
        }

        $unquotedValue = array_map('trim', explode('#', $rawValue, 2));
        return [
            new Value($unquotedValue[0], false),
            ($unquotedValue[1] ?? null) ? new Comment($unquotedValue[1]) : null
        ];
    }

}
