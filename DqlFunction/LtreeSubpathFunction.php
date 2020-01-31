<?php

declare(strict_types=1);

namespace DDL\LtreeExtensionBundle\DqlFunction;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use function sprintf;

class LtreeSubpathFunction extends FunctionNode
{
    /** @var Node */
    protected $first;
    /** @var Node */
    protected $second;
    /** @var Node */
    protected $third;

    public function getSql(SqlWalker $sqlWalker): string
    {
        return $this->third === null ?
            sprintf('subpath(%s, %s)', $this->first->dispatch($sqlWalker), $this->second->dispatch($sqlWalker)) :
            sprintf(
                'subpath(%s, %s, %s)',
                $this->first->dispatch($sqlWalker),
                $this->second->dispatch($sqlWalker),
                $this->third->dispatch($sqlWalker)
            );
    }

    public function parse(Parser $parser): void
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->first = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->second = $parser->ArithmeticPrimary();
        // parse third parameter if available
        if ($parser->getLexer()->lookahead['type'] === Lexer::T_COMMA) {
            $parser->match(Lexer::T_COMMA);
            $this->third = $parser->ScalarExpression();
        }

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}
