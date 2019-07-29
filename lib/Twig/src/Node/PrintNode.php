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
namespace ToolsetAdvancedExport\Twig\Node;

use ToolsetAdvancedExport\Twig\Compiler;
use ToolsetAdvancedExport\Twig\Node\Expression\AbstractExpression;
/**
 * Represents a node that outputs an expression.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class PrintNode extends \ToolsetAdvancedExport\Twig\Node\Node implements \ToolsetAdvancedExport\Twig\Node\NodeOutputInterface
{
    public function __construct(\ToolsetAdvancedExport\Twig\Node\Expression\AbstractExpression $expr, $lineno, $tag = null)
    {
        parent::__construct(['expr' => $expr], [], $lineno, $tag);
    }
    public function compile(\ToolsetAdvancedExport\Twig\Compiler $compiler)
    {
        $compiler->addDebugInfo($this)->write('echo ')->subcompile($this->getNode('expr'))->raw(";\n");
    }
}
\class_alias('ToolsetAdvancedExport\\Twig\\Node\\PrintNode', 'ToolsetAdvancedExport\\Twig_Node_Print');
