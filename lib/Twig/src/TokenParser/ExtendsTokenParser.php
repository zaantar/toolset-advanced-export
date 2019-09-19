<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 * (c) Armin Ronacher
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ToolsetAdvancedExport\Twig\TokenParser;

use ToolsetAdvancedExport\Twig\Error\SyntaxError;
use ToolsetAdvancedExport\Twig\Node\Node;
use ToolsetAdvancedExport\Twig\Token;
/**
 * Extends a template by another one.
 *
 *  {% extends "base.html" %}
 *
 * @final
 */
class ExtendsTokenParser extends \ToolsetAdvancedExport\Twig\TokenParser\AbstractTokenParser
{
    public function parse(\ToolsetAdvancedExport\Twig\Token $token)
    {
        $stream = $this->parser->getStream();
        if ($this->parser->peekBlockStack()) {
            throw new \ToolsetAdvancedExport\Twig\Error\SyntaxError('Cannot use "extend" in a block.', $token->getLine(), $stream->getSourceContext());
        } elseif (!$this->parser->isMainScope()) {
            throw new \ToolsetAdvancedExport\Twig\Error\SyntaxError('Cannot use "extend" in a macro.', $token->getLine(), $stream->getSourceContext());
        }
        if (null !== $this->parser->getParent()) {
            throw new \ToolsetAdvancedExport\Twig\Error\SyntaxError('Multiple extends tags are forbidden.', $token->getLine(), $stream->getSourceContext());
        }
        $this->parser->setParent($this->parser->getExpressionParser()->parseExpression());
        $stream->expect(\ToolsetAdvancedExport\Twig\Token::BLOCK_END_TYPE);
        return new \ToolsetAdvancedExport\Twig\Node\Node();
    }
    public function getTag()
    {
        return 'extends';
    }
}
\class_alias('ToolsetAdvancedExport\\Twig\\TokenParser\\ExtendsTokenParser', 'ToolsetAdvancedExport\\Twig_TokenParser_Extends');
