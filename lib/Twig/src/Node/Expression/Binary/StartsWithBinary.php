<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ToolsetAdvancedExport\Twig\Node\Expression\Binary;

use ToolsetAdvancedExport\Twig\Compiler;
class StartsWithBinary extends \ToolsetAdvancedExport\Twig\Node\Expression\Binary\AbstractBinary
{
    public function compile(\ToolsetAdvancedExport\Twig\Compiler $compiler)
    {
        $left = $compiler->getVarName();
        $right = $compiler->getVarName();
        $compiler->raw(\sprintf('(is_string($%s = ', $left))->subcompile($this->getNode('left'))->raw(\sprintf(') && is_string($%s = ', $right))->subcompile($this->getNode('right'))->raw(\sprintf(') && (\'\' === $%2$s || 0 === strpos($%1$s, $%2$s)))', $left, $right));
    }
    public function operator(\ToolsetAdvancedExport\Twig\Compiler $compiler)
    {
        return $compiler->raw('');
    }
}
\class_alias('ToolsetAdvancedExport\\Twig\\Node\\Expression\\Binary\\StartsWithBinary', 'ToolsetAdvancedExport\\Twig_Node_Expression_Binary_StartsWith');
