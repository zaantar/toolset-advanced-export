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

use ToolsetAdvancedExport\Twig\Node\WithNode;
use ToolsetAdvancedExport\Twig\Token;
/**
 * Creates a nested scope.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @final
 */
class WithTokenParser extends \ToolsetAdvancedExport\Twig\TokenParser\AbstractTokenParser
{
    public function parse(\ToolsetAdvancedExport\Twig\Token $token)
    {
        $stream = $this->parser->getStream();
        $variables = null;
        $only = \false;
        if (!$stream->test(\ToolsetAdvancedExport\Twig\Token::BLOCK_END_TYPE)) {
            $variables = $this->parser->getExpressionParser()->parseExpression();
            $only = $stream->nextIf(\ToolsetAdvancedExport\Twig\Token::NAME_TYPE, 'only');
        }
        $stream->expect(\ToolsetAdvancedExport\Twig\Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse([$this, 'decideWithEnd'], \true);
        $stream->expect(\ToolsetAdvancedExport\Twig\Token::BLOCK_END_TYPE);
        return new \ToolsetAdvancedExport\Twig\Node\WithNode($body, $variables, $only, $token->getLine(), $this->getTag());
    }
    public function decideWithEnd(\ToolsetAdvancedExport\Twig\Token $token)
    {
        return $token->test('endwith');
    }
    public function getTag()
    {
        return 'with';
    }
}
\class_alias('ToolsetAdvancedExport\\Twig\\TokenParser\\WithTokenParser', 'ToolsetAdvancedExport\\Twig_TokenParser_With');
