<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ToolsetAdvancedExport\Twig\TokenParser;

use ToolsetAdvancedExport\Twig\Node\DoNode;
use ToolsetAdvancedExport\Twig\Token;
/**
 * Evaluates an expression, discarding the returned value.
 *
 * @final
 */
class DoTokenParser extends \ToolsetAdvancedExport\Twig\TokenParser\AbstractTokenParser
{
    public function parse(\ToolsetAdvancedExport\Twig\Token $token)
    {
        $expr = $this->parser->getExpressionParser()->parseExpression();
        $this->parser->getStream()->expect(\ToolsetAdvancedExport\Twig\Token::BLOCK_END_TYPE);
        return new \ToolsetAdvancedExport\Twig\Node\DoNode($expr, $token->getLine(), $this->getTag());
    }
    public function getTag()
    {
        return 'do';
    }
}
\class_alias('ToolsetAdvancedExport\\Twig\\TokenParser\\DoTokenParser', 'ToolsetAdvancedExport\\Twig_TokenParser_Do');
