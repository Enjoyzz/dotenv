<?php

declare(strict_types=1);


namespace Enjoys\Dotenv\Parser\Lines;


final class Lines
{

    /**
     * @var string[]
     */
    private array $output = [];

    /**
     * @param string[] $rawLines
     */
    private function __construct(private array $rawLines)
    {
    }

    /**
     * @param string[] $rawLines
     * @return string[]
     */
    public static function handle(array $rawLines): array
    {
        $linesHandler = new self($rawLines);
        $linesHandler->process();
        return $linesHandler->getOutput();
    }


    /**
     * @return string[]
     */
    private function getOutput(): array
    {
        return $this->output;
    }

    private function process(): void
    {
        $multiline = false;
        $multilineBuffer = [];

        foreach ($this->rawLines as $line) {
            /** @psalm-suppress MixedArgumentTypeCoercion */
            [$multiline, $line, $multilineBuffer] = $this->multilineProcess($multiline, $line, $multilineBuffer);

            if (!$multiline) {

                $this->output[] = $line;
            }
        }

    }

    /**
     * @param bool $multiline
     * @param string $line
     * @param string[] $buffer
     * @return array{0: bool, 1: string, 2: array}
     */
    private function multilineProcess(bool $multiline, string $line, array $buffer): array
    {
        $started = false;

        if ($multiline ===false && $started = $this->looksLikeMultilineStart($line)) {
            $multiline = true;
        }

        if ($multiline) {

            $buffer[] = $line;

            if ($this->looksLikeMultilineStop($line, $started)) {

                $multiline = false;
                $line = \implode("\\n", $buffer);
                $buffer = [];
            }
        }

        return [$multiline, $line, $buffer];
    }

    private function looksLikeMultilineStart(string $line): bool
    {

        if (\mb_strpos($line, '="', 0, 'UTF-8') && !$this->looksLikeMultilineStop($line, true)){
            return true;
        }
        return false;
    }

    private function looksLikeMultilineStop(string $line, bool $started): bool
    {
        if ($line === '"') {
            return true;
        }

        $result = \preg_match_all('/(?=([^\\\\]"))/', \str_replace('\\\\', '', $line));
        return $started ? $result > 1 : $result >= 1;
    }

}
