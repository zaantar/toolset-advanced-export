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
class MulBinary extends \ToolsetAdvancedExport\Twig\Node\Expression\Binary\AbstractBinary
{
    public function operator(\ToolsetAdvancedExport\Twig\Compiler $compiler)
    {
        return $compiler->raw('*');
    }
}
\class_alias('ToolsetAdvancedExport\\Twig\\Node\\Expression\\Binary\\MulBinary', 'ToolsetAdvancedExport\\Twig_Node_Expression_Binary_Mul');
