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

use ToolsetAdvancedExport\Twig\Error\SyntaxError;
use ToolsetAdvancedExport\Twig\Node\Expression\ConstantExpression;
use ToolsetAdvancedExport\Twig\Node\Node;
use ToolsetAdvancedExport\Twig\Token;
/**
 * Imports blocks defined in another template into the current template.
 *
 *    {% extends "base.html" %}
 *
 *    {% use "blocks.html" %}
 *
 *    {% block title %}{% endblock %}
 *    {% block content %}{% endblock %}
 *
 * @see https://twig.symfony.com/doc/templates.html#horizontal-reuse for details.
 *
 * @final
 */
class UseTokenParser extends \ToolsetAdvancedExport\Twig\TokenParser\AbstractTokenParser
{
    public function parse(\ToolsetAdvancedExport\Twig\Token $token)
    {
        $template = $this->parser->getExpressionParser()->parseExpression();
        $stream = $this->parser->getStream();
        if (!$template instanceof \ToolsetAdvancedExport\Twig\Node\Expression\ConstantExpression) {
            throw new \ToolsetAdvancedExport\Twig\Error\SyntaxError('The template references in a "use" statement must be a string.', $stream->getCurrent()->getLine(), $stream->getSourceContext());
        }
        $targets = [];
        if ($stream->nextIf('with')) {
            do {
                $name = $stream->expect(\ToolsetAdvancedExport\Twig\Token::NAME_TYPE)->getValue();
                $alias = $name;
                if ($stream->nextIf('as')) {
                    $alias = $stream->expect(\ToolsetAdvancedExport\Twig\Token::NAME_TYPE)->getValue();
                }
                $targets[$name] = new \ToolsetAdvancedExport\Twig\Node\Expression\ConstantExpression($alias, -1);
                if (!$stream->nextIf(\ToolsetAdvancedExport\Twig\Token::PUNCTUATION_TYPE, ',')) {
                    break;
                }
            } while (\true);
        }
        $stream->expect(\ToolsetAdvancedExport\Twig\Token::BLOCK_END_TYPE);
        $this->parser->addTrait(new \ToolsetAdvancedExport\Twig\Node\Node(['template' => $template, 'targets' => new \ToolsetAdvancedExport\Twig\Node\Node($targets)]));
        return new \ToolsetAdvancedExport\Twig\Node\Node();
    }
    public function getTag()
    {
        return 'use';
    }
}
\class_alias('ToolsetAdvancedExport\\Twig\\TokenParser\\UseTokenParser', 'ToolsetAdvancedExport\\Twig_TokenParser_Use');
