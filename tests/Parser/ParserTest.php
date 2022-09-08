<?php

declare(strict_types=1);

namespace Parser;

use Enjoys\Dotenv\Exception\InvalidArgumentException;
use Enjoys\Dotenv\Parser\Lines\EnvLine;
use Enjoys\Dotenv\Parser\Parser;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{

    /**
     * @dataProvider dataForTestSplitContentOnRawArray
     */
    public function testSplitContentOnRawArray($input, $expect)
    {
        if ($expect === false) {
            $this->expectException(InvalidArgumentException::class);
        }
        $parser = new Parser();
        $this->assertSame(
            $expect,
            array_map(fn($item) => $item->__toString(), iterator_to_array($parser->parseLines($input)))
        );
    }

    public function dataForTestSplitContentOnRawArray(): array
    {
        return [
            ["", ['']],
            ["A", ['A']],
            ["A    \r\n    B  ", ['A', 'B']],
            ["A\rB", ['A', 'B']],
            ["A\rB", ['A', 'B']],
            ["A\x0bB", ['A', 'B']],
            ["A\fB", ['A', 'B']],
            ["A\x85B", ['A', 'B']],
        ];
    }


    public function testParse()
    {
        $parser = new Parser();
        $lines = array_map(fn($item) => $item->__toString(),
            iterator_to_array(
                $parser->parseLines(
                    <<<ENV
# comment

VAR1 = value  1
VAR2=value2#comment
VAR3 = "     value3 #not comment"                comment
ENV
                )
            ));

        $this->assertSame([
            '# comment',
            '',
            'VAR1=value  1',
            'VAR2=value2 #comment',
            'VAR3="     value3 #not comment" #comment'
        ], $lines);
    }

    public function testParseEnvLines()
    {
        $parser = new Parser();
        $envLines = [];
        foreach (
            $parser->parseLines(
                <<<ENV
VAR1 = value # comment #2
ENV
            ) as $parseLine
        ) {
            if ($parseLine instanceof EnvLine) {
                $envLines[$parseLine->getKey()->getValue()] = $parseLine;
            }
        }
        $this->assertCount(1, $envLines);
        $this->assertSame('VAR1', $envLines['VAR1']->getKey()->getValue());
        $this->assertSame('value', $envLines['VAR1']->getValue()->getValue());
        $this->assertSame('comment #2', $envLines['VAR1']->getComment()->getValue());
    }


}
