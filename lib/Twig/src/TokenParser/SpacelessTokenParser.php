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

use ToolsetAdvancedExport\Twig\Node\SpacelessNode;
use ToolsetAdvancedExport\Twig\Token;
/**
 * Remove whitespaces between HTML tags.
 *
 *   {% spaceless %}
 *      <div>
 *          <strong>foo</strong>
 *      </div>
 *   {% endspaceless %}
 *   {# output will be <div><strong>foo</strong></div> #}
 *
 * @final
 */
class SpacelessTokenParser extends \ToolsetAdvancedExport\Twig\TokenParser\AbstractTokenParser
{
    public function parse(\ToolsetAdvancedExport\Twig\Token $token)
    {
        $lineno = $token->getLine();
        $this->parser->getStream()->expect(\ToolsetAdvancedExport\Twig\Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse([$this, 'decideSpacelessEnd'], \true);
        $this->parser->getStream()->expect(\ToolsetAdvancedExport\Twig\Token::BLOCK_END_TYPE);
        return new \ToolsetAdvancedExport\Twig\Node\SpacelessNode($body, $lineno, $this->getTag());
    }
    public function decideSpacelessEnd(\ToolsetAdvancedExport\Twig\Token $token)
    {
        return $token->test('endspaceless');
    }
    public function getTag()
    {
        return 'spaceless';
    }
}
\class_alias('ToolsetAdvancedExport\\Twig\\TokenParser\\SpacelessTokenParser', 'ToolsetAdvancedExport\\Twig_TokenParser_Spaceless');
