<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ToolsetAdvancedExport\Twig\Node\Expression\Filter;

use ToolsetAdvancedExport\Twig\Compiler;
use ToolsetAdvancedExport\Twig\Node\Expression\ConditionalExpression;
use ToolsetAdvancedExport\Twig\Node\Expression\ConstantExpression;
use ToolsetAdvancedExport\Twig\Node\Expression\FilterExpression;
use ToolsetAdvancedExport\Twig\Node\Expression\GetAttrExpression;
use ToolsetAdvancedExport\Twig\Node\Expression\NameExpression;
use ToolsetAdvancedExport\Twig\Node\Expression\Test\DefinedTest;
use ToolsetAdvancedExport\Twig\Node\Node;
/**
 * Returns the value or the default value when it is undefined or empty.
 *
 *  {{ var.foo|default('foo item on var is not defined') }}
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class DefaultFilter extends \ToolsetAdvancedExport\Twig\Node\Expression\FilterExpression
{
    public function __construct(\ToolsetAdvancedExport\Twig_NodeInterface $node, \ToolsetAdvancedExport\Twig\Node\Expression\ConstantExpression $filterName, \ToolsetAdvancedExport\Twig_NodeInterface $arguments, $lineno, $tag = null)
    {
        $default = new \ToolsetAdvancedExport\Twig\Node\Expression\FilterExpression($node, new \ToolsetAdvancedExport\Twig\Node\Expression\ConstantExpression('default', $node->getTemplateLine()), $arguments, $node->getTemplateLine());
        if ('default' === $filterName->getAttribute('value') && ($node instanceof \ToolsetAdvancedExport\Twig\Node\Expression\NameExpression || $node instanceof \ToolsetAdvancedExport\Twig\Node\Expression\GetAttrExpression)) {
            $test = new \ToolsetAdvancedExport\Twig\Node\Expression\Test\DefinedTest(clone $node, 'defined', new \ToolsetAdvancedExport\Twig\Node\Node(), $node->getTemplateLine());
            $false = \count($arguments) ? $arguments->getNode(0) : new \ToolsetAdvancedExport\Twig\Node\Expression\ConstantExpression('', $node->getTemplateLine());
            $node = new \ToolsetAdvancedExport\Twig\Node\Expression\ConditionalExpression($test, $default, $false, $node->getTemplateLine());
        } else {
            $node = $default;
        }
        parent::__construct($node, $filterName, $arguments, $lineno, $tag);
    }
    public function compile(\ToolsetAdvancedExport\Twig\Compiler $compiler)
    {
        $compiler->subcompile($this->getNode('node'));
    }
}
\class_alias('ToolsetAdvancedExport\\Twig\\Node\\Expression\\Filter\\DefaultFilter', 'ToolsetAdvancedExport\\Twig_Node_Expression_Filter_Default');
