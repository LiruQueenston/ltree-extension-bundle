<?php

declare(strict_types=1);

namespace DDL\LtreeExtensionBundle\DqlFunction;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use function sprintf;

class LtreeNlevelFunction extends FunctionNode
{
    /** @var Node */
    protected $tree;

    public function getSql(SqlWalker $sqlWalker): string
    {
        return sprintf('nlevel(%s)', $this->tree->dispatch($sqlWalker));
    }

    public function parse(Parser $parser): void
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->tree = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}
