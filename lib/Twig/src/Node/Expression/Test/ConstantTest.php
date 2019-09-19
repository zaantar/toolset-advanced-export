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
 * Checks if a variable is the exact same value as a constant.
 *
 *    {% if post.status is constant('Post::PUBLISHED') %}
 *      the status attribute is exactly the same as Post::PUBLISHED
 *    {% endif %}
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ConstantTest extends \ToolsetAdvancedExport\Twig\Node\Expression\TestExpression
{
    public function compile(\ToolsetAdvancedExport\Twig\Compiler $compiler)
    {
        $compiler->raw('(')->subcompile($this->getNode('node'))->raw(' === constant(');
        if ($this->getNode('arguments')->hasNode(1)) {
            $compiler->raw('get_class(')->subcompile($this->getNode('arguments')->getNode(1))->raw(')."::".');
        }
        $compiler->subcompile($this->getNode('arguments')->getNode(0))->raw('))');
    }
}
\class_alias('ToolsetAdvancedExport\\Twig\\Node\\Expression\\Test\\ConstantTest', 'ToolsetAdvancedExport\\Twig_Node_Expression_Test_Constant');
