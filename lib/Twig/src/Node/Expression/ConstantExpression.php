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
class ConstantExpression extends \ToolsetAdvancedExport\Twig\Node\Expression\AbstractExpression
{
    public function __construct($value, $lineno)
    {
        parent::__construct([], ['value' => $value], $lineno);
    }
    public function compile(\ToolsetAdvancedExport\Twig\Compiler $compiler)
    {
        $compiler->repr($this->getAttribute('value'));
    }
}
\class_alias('ToolsetAdvancedExport\\Twig\\Node\\Expression\\ConstantExpression', 'ToolsetAdvancedExport\\Twig_Node_Expression_Constant');
