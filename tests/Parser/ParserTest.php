<?php

declare(strict_types=1);

namespace Parser;

use Enjoys\Dotenv\Exception\InvalidArgumentException;
use Enjoys\Dotenv\Parser\Lines\CommentLine;
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
//            ["A\x85B", ['A', 'B']],
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

    public function testCommentsWithSpecificSymbols()
    {
        $parser = new Parser();
        $structure = $parser->parseStructure(file_get_contents(__DIR__.'/../fixtures/.comment_with_specific_symbols'));

        /** @var CommentLine $comment */
        $comment = $structure[0];
        $this->assertSame(' comment with "Å" symbol', $comment->getComment());

        /** @var CommentLine $comment */
        $comment = $structure[1];
        $this->assertSame(' comment with "x" symbol', $comment->getComment());

        $this->assertSame([0,1, 'VAR', 2], array_keys($structure));
    }


}
