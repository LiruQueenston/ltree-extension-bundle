<?php

declare(strict_types=1);

namespace DDL\Tests\DqlFunction;

use DDL\LtreeExtensionBundle\DqlFunction\LtreeConcatFunction;
use Doctrine\ORM\Query\AST\ParenthesisExpression;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use PHPUnit\Framework\TestCase;

class LtreeConcatFunctionTest extends TestCase
{
    /** @var LtreeConcatFunction */
    private $ltreeConcat;

    public function setUp(): void
    {
        $this->ltreeConcat = new LtreeConcatFunction('test');
    }

    public function testFunction(): void
    {
        $parser = $this->prophesize(Parser::class);
        $expr = $this->prophesize(ParenthesisExpression::class);

        $parser->match()->shouldBeCalled()->withArguments([Lexer::T_IDENTIFIER]);
        $parser->match()->shouldBeCalled()->withArguments([Lexer::T_OPEN_PARENTHESIS]);
        $parser->ArithmeticPrimary()->shouldBeCalled()->willReturn($expr->reveal());
        $parser->match()->shouldBeCalled()->withArguments([Lexer::T_COMMA]);
        $parser->ArithmeticPrimary()->shouldBeCalled()->willReturn($expr->reveal());
        $parser->match()->shouldBeCalled()->withArguments([Lexer::T_CLOSE_PARENTHESIS]);
        $sqlWalker = $this->prophesize(SqlWalker::class);

        $this->ltreeConcat->parse($parser->reveal());
        $expr->dispatch()->shouldBeCalledTimes(2)->withArguments([$sqlWalker->reveal()])->willReturn('test');
        $this->assertEquals(
            '(test || test)',
            $this->ltreeConcat->getSql($sqlWalker->reveal())
        );
    }
}
