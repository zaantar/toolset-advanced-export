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

use ToolsetAdvancedExport\Twig\Node\FlushNode;
use ToolsetAdvancedExport\Twig\Token;
/**
 * Flushes the output to the client.
 *
 * @see flush()
 *
 * @final
 */
class FlushTokenParser extends \ToolsetAdvancedExport\Twig\TokenParser\AbstractTokenParser
{
    public function parse(\ToolsetAdvancedExport\Twig\Token $token)
    {
        $this->parser->getStream()->expect(\ToolsetAdvancedExport\Twig\Token::BLOCK_END_TYPE);
        return new \ToolsetAdvancedExport\Twig\Node\FlushNode($token->getLine(), $this->getTag());
    }
    public function getTag()
    {
        return 'flush';
    }
}
\class_alias('ToolsetAdvancedExport\\Twig\\TokenParser\\FlushTokenParser', 'ToolsetAdvancedExport\\Twig_TokenParser_Flush');
