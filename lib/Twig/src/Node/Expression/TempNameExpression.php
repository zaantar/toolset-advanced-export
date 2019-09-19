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
class TempNameExpression extends \ToolsetAdvancedExport\Twig\Node\Expression\AbstractExpression
{
    public function __construct($name, $lineno)
    {
        parent::__construct([], ['name' => $name], $lineno);
    }
    public function compile(\ToolsetAdvancedExport\Twig\Compiler $compiler)
    {
        $compiler->raw('$_')->raw($this->getAttribute('name'))->raw('_');
    }
}
\class_alias('ToolsetAdvancedExport\\Twig\\Node\\Expression\\TempNameExpression', 'ToolsetAdvancedExport\\Twig_Node_Expression_TempName');
