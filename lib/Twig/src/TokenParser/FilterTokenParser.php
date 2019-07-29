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

use ToolsetAdvancedExport\Twig\Node\BlockNode;
use ToolsetAdvancedExport\Twig\Node\Expression\BlockReferenceExpression;
use ToolsetAdvancedExport\Twig\Node\Expression\ConstantExpression;
use ToolsetAdvancedExport\Twig\Node\PrintNode;
use ToolsetAdvancedExport\Twig\Token;
/**
 * Filters a section of a template by applying filters.
 *
 *   {% filter upper %}
 *      This text becomes uppercase
 *   {% endfilter %}
 *
 * @final
 */
class FilterTokenParser extends \ToolsetAdvancedExport\Twig\TokenParser\AbstractTokenParser
{
    public function parse(\ToolsetAdvancedExport\Twig\Token $token)
    {
        $name = $this->parser->getVarName();
        $ref = new \ToolsetAdvancedExport\Twig\Node\Expression\BlockReferenceExpression(new \ToolsetAdvancedExport\Twig\Node\Expression\ConstantExpression($name, $token->getLine()), null, $token->getLine(), $this->getTag());
        $filter = $this->parser->getExpressionParser()->parseFilterExpressionRaw($ref, $this->getTag());
        $this->parser->getStream()->expect(\ToolsetAdvancedExport\Twig\Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse([$this, 'decideBlockEnd'], \true);
        $this->parser->getStream()->expect(\ToolsetAdvancedExport\Twig\Token::BLOCK_END_TYPE);
        $block = new \ToolsetAdvancedExport\Twig\Node\BlockNode($name, $body, $token->getLine());
        $this->parser->setBlock($name, $block);
        return new \ToolsetAdvancedExport\Twig\Node\PrintNode($filter, $token->getLine(), $this->getTag());
    }
    public function decideBlockEnd(\ToolsetAdvancedExport\Twig\Token $token)
    {
        return $token->test('endfilter');
    }
    public function getTag()
    {
        return 'filter';
    }
}
\class_alias('ToolsetAdvancedExport\\Twig\\TokenParser\\FilterTokenParser', 'ToolsetAdvancedExport\\Twig_TokenParser_Filter');
