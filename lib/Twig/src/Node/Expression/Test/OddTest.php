<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ToolsetAdvancedExport\Twig\Node\Expression\Test;

use ToolsetAdvancedExport\Twig\Compiler;
use ToolsetAdvancedExport\Twig\Node\Expression\TestExpression;
/**
 * Checks if a number is odd.
 *
 *  {{ var is odd }}
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class OddTest extends \ToolsetAdvancedExport\Twig\Node\Expression\TestExpression
{
    public function compile(\ToolsetAdvancedExport\Twig\Compiler $compiler)
    {
        $compiler->raw('(')->subcompile($this->getNode('node'))->raw(' % 2 == 1')->raw(')');
    }
}
\class_alias('ToolsetAdvancedExport\\Twig\\Node\\Expression\\Test\\OddTest', 'ToolsetAdvancedExport\\Twig_Node_Expression_Test_Odd');
