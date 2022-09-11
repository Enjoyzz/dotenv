<?php

declare(strict_types=1);

namespace Parser;

use Enjoys\Dotenv\Parser\Builder;
use Enjoys\Dotenv\Parser\Env\Comment;
use Enjoys\Dotenv\Parser\Env\Key;
use Enjoys\Dotenv\Parser\Env\Value;
use Enjoys\Dotenv\Parser\Lines\CommentLine;
use Enjoys\Dotenv\Parser\Lines\EmptyLine;
use Enjoys\Dotenv\Parser\Lines\EnvLine;
use Enjoys\Dotenv\Parser\Parser;
use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
    private string $input = <<<ENV
# Comment
VAR1 = value1
VAR2 = value 2

VAR3 = "value #3" comment
VAR4 = value #3 comment
VAR5='value \'5'
VAR6="value \"6"           #comment
ENV;

    private string $expect = <<<ENV
# Comment
VAR1=value1
VAR2=value 2

VAR3="value #3" #comment
VAR4=value #3 comment
VAR5='value \'5'
VAR6="value \"6" #comment
ENV;

    public function testBuild()
    {
        $parser = new Parser();
        ;

        $builder = new Builder($parser->parseStructure($this->input));
        $this->assertSame($this->expect, $builder->build());
    }

    public function testBuildFromCode()
    {
        $lines = [];
        $lines[] = new EnvLine(new Key('TEST1'));
        $lines[] = new EmptyLine();
        $lines[] = new EnvLine(
            new Key('TEST2'),
            new Value('VALUE #2 @builder', '"'),
            new Comment('manually quote')
        );
        $lines[] = new CommentLine('GROUP COMMENT');
        $lines[] = new EnvLine(
            new Key('TEST2'),
            new Value('VALUE #2 @builder', null),
            new Comment('auto-quote')
        );

        $builder = new Builder($lines);
        $this->assertSame(
            <<<ENV
TEST1

TEST2="VALUE #2 @builder" #manually quote
#GROUP COMMENT
TEST2="VALUE #2 @builder" #auto-quote
ENV
            ,
            $builder->build()
        );
    }
}
