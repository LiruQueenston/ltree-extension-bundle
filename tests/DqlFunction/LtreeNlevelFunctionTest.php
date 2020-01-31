<?php

declare(strict_types=1);

namespace DDL\Tests\DqlFunction;

use DDL\LtreeExtensionBundle\DqlFunction\LtreeNlevelFunction;
use Doctrine\ORM\Query\AST\ParenthesisExpression;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use PHPUnit\Framework\TestCase;

class LtreeNlevelFunctionTest extends TestCase
{
    /** @var LtreeNlevelFunction */
    private $ltreeNlevelFunction;

    public function setUp(): void
    {
        $this->ltreeNlevelFunction = new LtreeNlevelFunction('test');
    }

    public function testFunction(): void
    {
        $parser = $this->prophesize(Parser::class);
        $expr = $this->prophesize(ParenthesisExpression::class);

        $parser->match()->shouldBeCalled()->withArguments([Lexer::T_IDENTIFIER]);
        $parser->match()->shouldBeCalled()->withArguments([Lexer::T_OPEN_PARENTHESIS]);
        $parser->ArithmeticPrimary()->shouldBeCalled()->willReturn($expr->reveal());
        $parser->match()->shouldBeCalled()->withArguments([Lexer::T_CLOSE_PARENTHESIS]);
        $sqlWalker = $this->prophesize(SqlWalker::class);

        $this->ltreeNlevelFunction->parse($parser->reveal());
        $expr->dispatch()->shouldBeCalled()->withArguments([$sqlWalker->reveal()])->willReturn('test');
        $this->assertEquals(
            'nlevel(test)',
            $this->ltreeNlevelFunction->getSql($sqlWalker->reveal())
        );
    }
}
