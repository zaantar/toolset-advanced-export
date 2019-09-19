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

use ToolsetAdvancedExport\Twig\Node\EmbedNode;
use ToolsetAdvancedExport\Twig\Node\Expression\ConstantExpression;
use ToolsetAdvancedExport\Twig\Node\Expression\NameExpression;
use ToolsetAdvancedExport\Twig\Token;
/**
 * Embeds a template.
 *
 * @final
 */
class EmbedTokenParser extends \ToolsetAdvancedExport\Twig\TokenParser\IncludeTokenParser
{
    public function parse(\ToolsetAdvancedExport\Twig\Token $token)
    {
        $stream = $this->parser->getStream();
        $parent = $this->parser->getExpressionParser()->parseExpression();
        list($variables, $only, $ignoreMissing) = $this->parseArguments();
        $parentToken = $fakeParentToken = new \ToolsetAdvancedExport\Twig\Token(\ToolsetAdvancedExport\Twig\Token::STRING_TYPE, '__parent__', $token->getLine());
        if ($parent instanceof \ToolsetAdvancedExport\Twig\Node\Expression\ConstantExpression) {
            $parentToken = new \ToolsetAdvancedExport\Twig\Token(\ToolsetAdvancedExport\Twig\Token::STRING_TYPE, $parent->getAttribute('value'), $token->getLine());
        } elseif ($parent instanceof \ToolsetAdvancedExport\Twig\Node\Expression\NameExpression) {
            $parentToken = new \ToolsetAdvancedExport\Twig\Token(\ToolsetAdvancedExport\Twig\Token::NAME_TYPE, $parent->getAttribute('name'), $token->getLine());
        }
        // inject a fake parent to make the parent() function work
        $stream->injectTokens([new \ToolsetAdvancedExport\Twig\Token(\ToolsetAdvancedExport\Twig\Token::BLOCK_START_TYPE, '', $token->getLine()), new \ToolsetAdvancedExport\Twig\Token(\ToolsetAdvancedExport\Twig\Token::NAME_TYPE, 'extends', $token->getLine()), $parentToken, new \ToolsetAdvancedExport\Twig\Token(\ToolsetAdvancedExport\Twig\Token::BLOCK_END_TYPE, '', $token->getLine())]);
        $module = $this->parser->parse($stream, [$this, 'decideBlockEnd'], \true);
        // override the parent with the correct one
        if ($fakeParentToken === $parentToken) {
            $module->setNode('parent', $parent);
        }
        $this->parser->embedTemplate($module);
        $stream->expect(\ToolsetAdvancedExport\Twig\Token::BLOCK_END_TYPE);
        return new \ToolsetAdvancedExport\Twig\Node\EmbedNode($module->getTemplateName(), $module->getAttribute('index'), $variables, $only, $ignoreMissing, $token->getLine(), $this->getTag());
    }
    public function decideBlockEnd(\ToolsetAdvancedExport\Twig\Token $token)
    {
        return $token->test('endembed');
    }
    public function getTag()
    {
        return 'embed';
    }
}
\class_alias('ToolsetAdvancedExport\\Twig\\TokenParser\\EmbedTokenParser', 'ToolsetAdvancedExport\\Twig_TokenParser_Embed');
