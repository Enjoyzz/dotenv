<?php

declare(strict_types=1);

namespace Parser\Env;

use Enjoys\Dotenv\Parser\Env\Comment;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CommentTest extends TestCase
{

    #[DataProvider('data')]
    public function testCommentInEnv($value, $expect)
    {
        $comment = new Comment($value);
        $this->assertSame($expect, $comment->getValue());
        $this->assertSame(' #'.$expect, $comment->__toString());
    }

    public static function data()
    {
        return [
            ['      #comment', 'comment'],
            ['      comment', 'comment'],
            ['      com #ment', 'com #ment'],
        ];
    }
}
