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
use ToolsetAdvancedExport\Twig\Node\SetNode;
use ToolsetAdvancedExport\Twig\Token;
/**
 * Defines a variable.
 *
 *  {% set foo = 'foo' %}
 *  {% set foo = [1, 2] %}
 *  {% set foo = {'foo': 'bar'} %}
 *  {% set foo = 'foo' ~ 'bar' %}
 *  {% set foo, bar = 'foo', 'bar' %}
 *  {% set foo %}Some content{% endset %}
 *
 * @final
 */
class SetTokenParser extends \ToolsetAdvancedExport\Twig\TokenParser\AbstractTokenParser
{
    public function parse(\ToolsetAdvancedExport\Twig\Token $token)
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();
        $names = $this->parser->getExpressionParser()->parseAssignmentExpression();
        $capture = \false;
        if ($stream->nextIf(\ToolsetAdvancedExport\Twig\Token::OPERATOR_TYPE, '=')) {
            $values = $this->parser->getExpressionParser()->parseMultitargetExpression();
            $stream->expect(\ToolsetAdvancedExport\Twig\Token::BLOCK_END_TYPE);
            if (\count($names) !== \count($values)) {
                throw new \ToolsetAdvancedExport\Twig\Error\SyntaxError('When using set, you must have the same number of variables and assignments.', $stream->getCurrent()->getLine(), $stream->getSourceContext());
            }
        } else {
            $capture = \true;
            if (\count($names) > 1) {
                throw new \ToolsetAdvancedExport\Twig\Error\SyntaxError('When using set with a block, you cannot have a multi-target.', $stream->getCurrent()->getLine(), $stream->getSourceContext());
            }
            $stream->expect(\ToolsetAdvancedExport\Twig\Token::BLOCK_END_TYPE);
            $values = $this->parser->subparse([$this, 'decideBlockEnd'], \true);
            $stream->expect(\ToolsetAdvancedExport\Twig\Token::BLOCK_END_TYPE);
        }
        return new \ToolsetAdvancedExport\Twig\Node\SetNode($capture, $names, $values, $lineno, $this->getTag());
    }
    public function decideBlockEnd(\ToolsetAdvancedExport\Twig\Token $token)
    {
        return $token->test('endset');
    }
    public function getTag()
    {
        return 'set';
    }
}
\class_alias('ToolsetAdvancedExport\\Twig\\TokenParser\\SetTokenParser', 'ToolsetAdvancedExport\\Twig_TokenParser_Set');
