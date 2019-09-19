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
use ToolsetAdvancedExport\Twig\Node\Expression\AssignNameExpression;
use ToolsetAdvancedExport\Twig\Node\ImportNode;
use ToolsetAdvancedExport\Twig\Token;
/**
 * Imports macros.
 *
 *   {% from 'forms.html' import forms %}
 *
 * @final
 */
class FromTokenParser extends \ToolsetAdvancedExport\Twig\TokenParser\AbstractTokenParser
{
    public function parse(\ToolsetAdvancedExport\Twig\Token $token)
    {
        $macro = $this->parser->getExpressionParser()->parseExpression();
        $stream = $this->parser->getStream();
        $stream->expect(\ToolsetAdvancedExport\Twig\Token::NAME_TYPE, 'import');
        $targets = [];
        do {
            $name = $stream->expect(\ToolsetAdvancedExport\Twig\Token::NAME_TYPE)->getValue();
            $alias = $name;
            if ($stream->nextIf('as')) {
                $alias = $stream->expect(\ToolsetAdvancedExport\Twig\Token::NAME_TYPE)->getValue();
            }
            $targets[$name] = $alias;
            if (!$stream->nextIf(\ToolsetAdvancedExport\Twig\Token::PUNCTUATION_TYPE, ',')) {
                break;
            }
        } while (\true);
        $stream->expect(\ToolsetAdvancedExport\Twig\Token::BLOCK_END_TYPE);
        $var = new \ToolsetAdvancedExport\Twig\Node\Expression\AssignNameExpression($this->parser->getVarName(), $token->getLine());
        $node = new \ToolsetAdvancedExport\Twig\Node\ImportNode($macro, $var, $token->getLine(), $this->getTag());
        foreach ($targets as $name => $alias) {
            if ($this->parser->isReservedMacroName($name)) {
                throw new \ToolsetAdvancedExport\Twig\Error\SyntaxError(\sprintf('"%s" cannot be an imported macro as it is a reserved keyword.', $name), $token->getLine(), $stream->getSourceContext());
            }
            $this->parser->addImportedSymbol('function', $alias, 'get' . $name, $var);
        }
        return $node;
    }
    public function getTag()
    {
        return 'from';
    }
}
\class_alias('ToolsetAdvancedExport\\Twig\\TokenParser\\FromTokenParser', 'ToolsetAdvancedExport\\Twig_TokenParser_From');
