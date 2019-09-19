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
 * Checks if a number is even.
 *
 *  {{ var is even }}
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class EvenTest extends \ToolsetAdvancedExport\Twig\Node\Expression\TestExpression
{
    public function compile(\ToolsetAdvancedExport\Twig\Compiler $compiler)
    {
        $compiler->raw('(')->subcompile($this->getNode('node'))->raw(' % 2 == 0')->raw(')');
    }
}
\class_alias('ToolsetAdvancedExport\\Twig\\Node\\Expression\\Test\\EvenTest', 'ToolsetAdvancedExport\\Twig_Node_Expression_Test_Even');
