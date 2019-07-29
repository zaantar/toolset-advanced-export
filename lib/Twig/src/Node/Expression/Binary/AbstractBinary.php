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
namespace ToolsetAdvancedExport\Twig\Node\Expression\Binary;

use ToolsetAdvancedExport\Twig\Compiler;
use ToolsetAdvancedExport\Twig\Node\Expression\AbstractExpression;
abstract class AbstractBinary extends \ToolsetAdvancedExport\Twig\Node\Expression\AbstractExpression
{
    public function __construct(\ToolsetAdvancedExport\Twig_NodeInterface $left, \ToolsetAdvancedExport\Twig_NodeInterface $right, $lineno)
    {
        parent::__construct(['left' => $left, 'right' => $right], [], $lineno);
    }
    public function compile(\ToolsetAdvancedExport\Twig\Compiler $compiler)
    {
        $compiler->raw('(')->subcompile($this->getNode('left'))->raw(' ');
        $this->operator($compiler);
        $compiler->raw(' ')->subcompile($this->getNode('right'))->raw(')');
    }
    public abstract function operator(\ToolsetAdvancedExport\Twig\Compiler $compiler);
}
\class_alias('ToolsetAdvancedExport\\Twig\\Node\\Expression\\Binary\\AbstractBinary', 'ToolsetAdvancedExport\\Twig_Node_Expression_Binary');
