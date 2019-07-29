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
namespace ToolsetAdvancedExport\Twig\Node\Expression;

use ToolsetAdvancedExport\Twig\Compiler;
class AssignNameExpression extends \ToolsetAdvancedExport\Twig\Node\Expression\NameExpression
{
    public function compile(\ToolsetAdvancedExport\Twig\Compiler $compiler)
    {
        $compiler->raw('$context[')->string($this->getAttribute('name'))->raw(']');
    }
}
\class_alias('ToolsetAdvancedExport\\Twig\\Node\\Expression\\AssignNameExpression', 'ToolsetAdvancedExport\\Twig_Node_Expression_AssignName');
