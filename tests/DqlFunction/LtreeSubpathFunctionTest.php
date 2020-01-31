<?php

declare(strict_types=1);

namespace DDL\Tests\DqlFunction;

use DDL\LtreeExtensionBundle\DqlFunction\LtreeSubpathFunction;
use Doctrine\ORM\Query\AST\ParenthesisExpression;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use PHPUnit\Framework\TestCase;

class LtreeSubpathFunctionTest extends TestCase
{
    /** @var LtreeSubpathFunction */
    private $ltreeOperatorFunction;

    public function setUp(): void
    {
        $this->ltreeOperatorFunction = new LtreeSubpathFunction('test');
    }

    public function testTwoArgumentsFunction(): void
    {
        $parser = $this->prophesize(Parser::class);
        $expr = $this->prophesize(ParenthesisExpression::class);

        $parser->match()->shouldBeCalled()->withArguments([Lexer::T_IDENTIFIER]);
        $parser->match()->shouldBeCalled()->withArguments([Lexer::T_OPEN_PARENTHESIS]);
        $parser->ArithmeticPrimary()->shouldBeCalled()->willReturn($expr->reveal());
        $parser->match()->shouldBeCalled()->withArguments([Lexer::T_COMMA]);
        $parser->ArithmeticPrimary()->shouldBeCalled()->willReturn($expr->reveal());

        $lexer = $this->prophesize(Lexer::class);
        $parser->getLexer()->shouldBeCalled()->willReturn($lexer->reveal());

        $parser->match()->shouldBeCalled()->withArguments([Lexer::T_CLOSE_PARENTHESIS]);
        $sqlWalker = $this->prophesize(SqlWalker::class);

        $this->ltreeOperatorFunction->parse($parser->reveal());
        $expr->dispatch()->shouldBeCalledTimes(2)->withArguments([$sqlWalker->reveal()])->willReturn('test');
        $this->assertEquals(
            'subpath(test, test)',
            $this->ltreeOperatorFunction->getSql($sqlWalker->reveal())
        );
    }

    public function testThreeArgumentsFunction(): void
    {
        $parser = $this->prophesize(Parser::class);
        $expr = $this->prophesize(ParenthesisExpression::class);

        $parser->match()->shouldBeCalled()->withArguments([Lexer::T_IDENTIFIER]);
        $parser->match()->shouldBeCalled()->withArguments([Lexer::T_OPEN_PARENTHESIS]);
        $parser->ArithmeticPrimary()->shouldBeCalled()->willReturn($expr->reveal());
        $parser->match()->shouldBeCalled()->withArguments([Lexer::T_COMMA]);
        $parser->ArithmeticPrimary()->shouldBeCalled()->willReturn($expr->reveal());
        $lexer = $this->prophesize(Lexer::class)->reveal();
        $lexer->lookahead['type'] = Lexer::T_COMMA;
        $parser->getLexer()->shouldBeCalled()->willReturn($lexer);
        $parser->match()->shouldBeCalled()->withArguments([Lexer::T_COMMA]);
        $parser->ScalarExpression()->shouldBeCalled()->willReturn($expr->reveal());
        $parser->match()->shouldBeCalled()->withArguments([Lexer::T_CLOSE_PARENTHESIS]);
        $sqlWalker = $this->prophesize(SqlWalker::class);

        $this->ltreeOperatorFunction->parse($parser->reveal());
        $expr->dispatch()->shouldBeCalledTimes(3)->withArguments([$sqlWalker->reveal()])->willReturn('test');
        $this->assertEquals(
            'subpath(test, test, test)',
            $this->ltreeOperatorFunction->getSql($sqlWalker->reveal())
        );
    }
}
