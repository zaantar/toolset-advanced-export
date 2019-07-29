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
use ToolsetAdvancedExport\Twig\Node\BlockNode;
use ToolsetAdvancedExport\Twig\Node\BlockReferenceNode;
use ToolsetAdvancedExport\Twig\Node\Node;
use ToolsetAdvancedExport\Twig\Node\PrintNode;
use ToolsetAdvancedExport\Twig\Token;
/**
 * Marks a section of a template as being reusable.
 *
 *  {% block head %}
 *    <link rel="stylesheet" href="style.css" />
 *    <title>{% block title %}{% endblock %} - My Webpage</title>
 *  {% endblock %}
 *
 * @final
 */
class BlockTokenParser extends \ToolsetAdvancedExport\Twig\TokenParser\AbstractTokenParser
{
    public function parse(\ToolsetAdvancedExport\Twig\Token $token)
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();
        $name = $stream->expect(\ToolsetAdvancedExport\Twig\Token::NAME_TYPE)->getValue();
        if ($this->parser->hasBlock($name)) {
            throw new \ToolsetAdvancedExport\Twig\Error\SyntaxError(\sprintf("The block '%s' has already been defined line %d.", $name, $this->parser->getBlock($name)->getTemplateLine()), $stream->getCurrent()->getLine(), $stream->getSourceContext());
        }
        $this->parser->setBlock($name, $block = new \ToolsetAdvancedExport\Twig\Node\BlockNode($name, new \ToolsetAdvancedExport\Twig\Node\Node([]), $lineno));
        $this->parser->pushLocalScope();
        $this->parser->pushBlockStack($name);
        if ($stream->nextIf(\ToolsetAdvancedExport\Twig\Token::BLOCK_END_TYPE)) {
            $body = $this->parser->subparse([$this, 'decideBlockEnd'], \true);
            if ($token = $stream->nextIf(\ToolsetAdvancedExport\Twig\Token::NAME_TYPE)) {
                $value = $token->getValue();
                if ($value != $name) {
                    throw new \ToolsetAdvancedExport\Twig\Error\SyntaxError(\sprintf('Expected endblock for block "%s" (but "%s" given).', $name, $value), $stream->getCurrent()->getLine(), $stream->getSourceContext());
                }
            }
        } else {
            $body = new \ToolsetAdvancedExport\Twig\Node\Node([new \ToolsetAdvancedExport\Twig\Node\PrintNode($this->parser->getExpressionParser()->parseExpression(), $lineno)]);
        }
        $stream->expect(\ToolsetAdvancedExport\Twig\Token::BLOCK_END_TYPE);
        $block->setNode('body', $body);
        $this->parser->popBlockStack();
        $this->parser->popLocalScope();
        return new \ToolsetAdvancedExport\Twig\Node\BlockReferenceNode($name, $lineno, $this->getTag());
    }
    public function decideBlockEnd(\ToolsetAdvancedExport\Twig\Token $token)
    {
        return $token->test('endblock');
    }
    public function getTag()
    {
        return 'block';
    }
}
\class_alias('ToolsetAdvancedExport\\Twig\\TokenParser\\BlockTokenParser', 'ToolsetAdvancedExport\\Twig_TokenParser_Block');
