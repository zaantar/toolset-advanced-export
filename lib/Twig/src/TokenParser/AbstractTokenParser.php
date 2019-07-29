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

use ToolsetAdvancedExport\Twig\Parser;
/**
 * Base class for all token parsers.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
abstract class AbstractTokenParser implements \ToolsetAdvancedExport\Twig\TokenParser\TokenParserInterface
{
    /**
     * @var Parser
     */
    protected $parser;
    public function setParser(\ToolsetAdvancedExport\Twig\Parser $parser)
    {
        $this->parser = $parser;
    }
}
\class_alias('ToolsetAdvancedExport\\Twig\\TokenParser\\AbstractTokenParser', 'ToolsetAdvancedExport\\Twig_TokenParser');
