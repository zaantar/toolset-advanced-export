<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ToolsetAdvancedExport\Twig\Node\Expression;

use ToolsetAdvancedExport\Twig\Compiler;
use ToolsetAdvancedExport\Twig\Node\Node;
/**
 * @internal
 */
final class InlinePrint extends \ToolsetAdvancedExport\Twig\Node\Expression\AbstractExpression
{
    public function __construct(\ToolsetAdvancedExport\Twig\Node\Node $node, $lineno)
    {
        parent::__construct(['node' => $node], [], $lineno);
    }
    public function compile(\ToolsetAdvancedExport\Twig\Compiler $compiler)
    {
        $compiler->raw('print (')->subcompile($this->getNode('node'))->raw(')');
    }
}
