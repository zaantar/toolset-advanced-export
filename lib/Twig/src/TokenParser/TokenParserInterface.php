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
use ToolsetAdvancedExport\Twig\Parser;
use ToolsetAdvancedExport\Twig\Token;
/**
 * Interface implemented by token parsers.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface TokenParserInterface
{
    /**
     * Sets the parser associated with this token parser.
     */
    public function setParser(\ToolsetAdvancedExport\Twig\Parser $parser);
    /**
     * Parses a token and returns a node.
     *
     * @return \Twig_NodeInterface
     *
     * @throws SyntaxError
     */
    public function parse(\ToolsetAdvancedExport\Twig\Token $token);
    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag();
}
\class_alias('ToolsetAdvancedExport\\Twig\\TokenParser\\TokenParserInterface', 'ToolsetAdvancedExport\\Twig_TokenParserInterface');
// Ensure that the aliased name is loaded to keep BC for classes implementing the typehint with the old aliased name.
\class_exists('ToolsetAdvancedExport\\Twig\\Token');
\class_exists('ToolsetAdvancedExport\\Twig\\Parser');
