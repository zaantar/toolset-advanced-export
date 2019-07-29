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

use ToolsetAdvancedExport\Twig\Node\Expression\AssignNameExpression;
use ToolsetAdvancedExport\Twig\Node\ImportNode;
use ToolsetAdvancedExport\Twig\Token;
/**
 * Imports macros.
 *
 *   {% import 'forms.html' as forms %}
 *
 * @final
 */
class ImportTokenParser extends \ToolsetAdvancedExport\Twig\TokenParser\AbstractTokenParser
{
    public function parse(\ToolsetAdvancedExport\Twig\Token $token)
    {
        $macro = $this->parser->getExpressionParser()->parseExpression();
        $this->parser->getStream()->expect(\ToolsetAdvancedExport\Twig\Token::NAME_TYPE, 'as');
        $var = new \ToolsetAdvancedExport\Twig\Node\Expression\AssignNameExpression($this->parser->getStream()->expect(\ToolsetAdvancedExport\Twig\Token::NAME_TYPE)->getValue(), $token->getLine());
        $this->parser->getStream()->expect(\ToolsetAdvancedExport\Twig\Token::BLOCK_END_TYPE);
        $this->parser->addImportedSymbol('template', $var->getAttribute('name'));
        return new \ToolsetAdvancedExport\Twig\Node\ImportNode($macro, $var, $token->getLine(), $this->getTag());
    }
    public function getTag()
    {
        return 'import';
    }
}
\class_alias('ToolsetAdvancedExport\\Twig\\TokenParser\\ImportTokenParser', 'ToolsetAdvancedExport\\Twig_TokenParser_Import');
