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
class PosUnary extends \ToolsetAdvancedExport\Twig\Node\Expression\Unary\AbstractUnary
{
    public function operator(\ToolsetAdvancedExport\Twig\Compiler $compiler)
    {
        $compiler->raw('+');
    }
}
\class_alias('ToolsetAdvancedExport\\Twig\\Node\\Expression\\Unary\\PosUnary', 'ToolsetAdvancedExport\\Twig_Node_Expression_Unary_Pos');
