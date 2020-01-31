<?php

declare(strict_types=1);

namespace DDL\Tests\DqlFunction;

use DDL\LtreeExtensionBundle\DqlFunction\LtreeOperatorFunction;
use Doctrine\ORM\Query\AST\Literal;
use Doctrine\ORM\Query\AST\ParenthesisExpression;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use PHPUnit\Framework\TestCase;

class LtreeOperatorFunctionTest extends TestCase
{
    /** @var LtreeOperatorFunction */
    private $ltreeOperatorFunction;

    public function setUp(): void
    {
        $this->ltreeOperatorFunction = new LtreeOperatorFunction('test');
    }

    public function testFunction(): void
    {
        $parser = $this->prophesize(Parser::class);
        $expr = $this->prophesize(ParenthesisExpression::class);
        $operator = new Literal(Literal::STRING, '@>');

        $parser->match()->shouldBeCalled()->withArguments([Lexer::T_IDENTIFIER]);
        $parser->match()->shouldBeCalled()->withArguments([Lexer::T_OPEN_PARENTHESIS]);
        $parser->ArithmeticPrimary()->shouldBeCalled()->willReturn($expr->reveal());
        $parser->match()->shouldBeCalled()->withArguments([Lexer::T_COMMA]);
        $parser->StringPrimary()->shouldBeCalled()->willReturn($operator);
        $parser->match()->shouldBeCalled()->withArguments([Lexer::T_COMMA]);
        $parser->ArithmeticPrimary()->shouldBeCalled()->willReturn($expr->reveal());
        $parser->match()->shouldBeCalled()->withArguments([Lexer::T_CLOSE_PARENTHESIS]);
        $sqlWalker = $this->prophesize(SqlWalker::class);

        $this->ltreeOperatorFunction->parse($parser->reveal());
        $expr->dispatch()->shouldBeCalledTimes(2)->withArguments([$sqlWalker->reveal()])->willReturn('test');
        $this->assertEquals(
            '(test @> test)',
            $this->ltreeOperatorFunction->getSql($sqlWalker->reveal())
        );
    }
}
