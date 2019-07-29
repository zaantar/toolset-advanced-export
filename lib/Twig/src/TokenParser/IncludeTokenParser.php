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

use ToolsetAdvancedExport\Twig\Node\IncludeNode;
use ToolsetAdvancedExport\Twig\Token;
/**
 * Includes a template.
 *
 *   {% include 'header.html' %}
 *     Body
 *   {% include 'footer.html' %}
 */
class IncludeTokenParser extends \ToolsetAdvancedExport\Twig\TokenParser\AbstractTokenParser
{
    public function parse(\ToolsetAdvancedExport\Twig\Token $token)
    {
        $expr = $this->parser->getExpressionParser()->parseExpression();
        list($variables, $only, $ignoreMissing) = $this->parseArguments();
        return new \ToolsetAdvancedExport\Twig\Node\IncludeNode($expr, $variables, $only, $ignoreMissing, $token->getLine(), $this->getTag());
    }
    protected function parseArguments()
    {
        $stream = $this->parser->getStream();
        $ignoreMissing = \false;
        if ($stream->nextIf(\ToolsetAdvancedExport\Twig\Token::NAME_TYPE, 'ignore')) {
            $stream->expect(\ToolsetAdvancedExport\Twig\Token::NAME_TYPE, 'missing');
            $ignoreMissing = \true;
        }
        $variables = null;
        if ($stream->nextIf(\ToolsetAdvancedExport\Twig\Token::NAME_TYPE, 'with')) {
            $variables = $this->parser->getExpressionParser()->parseExpression();
        }
        $only = \false;
        if ($stream->nextIf(\ToolsetAdvancedExport\Twig\Token::NAME_TYPE, 'only')) {
            $only = \true;
        }
        $stream->expect(\ToolsetAdvancedExport\Twig\Token::BLOCK_END_TYPE);
        return [$variables, $only, $ignoreMissing];
    }
    public function getTag()
    {
        return 'include';
    }
}
\class_alias('ToolsetAdvancedExport\\Twig\\TokenParser\\IncludeTokenParser', 'ToolsetAdvancedExport\\Twig_TokenParser_Include');
