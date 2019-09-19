<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 * (c) Armin Ronacher
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ToolsetAdvancedExport\Twig\Node\Expression\Unary;

use ToolsetAdvancedExport\Twig\Compiler;
use ToolsetAdvancedExport\Twig\Node\Expression\AbstractExpression;
abstract class AbstractUnary extends \ToolsetAdvancedExport\Twig\Node\Expression\AbstractExpression
{
    public function __construct(\ToolsetAdvancedExport\Twig_NodeInterface $node, $lineno)
    {
        parent::__construct(['node' => $node], [], $lineno);
    }
    public function compile(\ToolsetAdvancedExport\Twig\Compiler $compiler)
    {
        $compiler->raw(' ');
        $this->operator($compiler);
        $compiler->subcompile($this->getNode('node'));
    }
    public abstract function operator(\ToolsetAdvancedExport\Twig\Compiler $compiler);
}
\class_alias('ToolsetAdvancedExport\\Twig\\Node\\Expression\\Unary\\AbstractUnary', 'ToolsetAdvancedExport\\Twig_Node_Expression_Unary');
