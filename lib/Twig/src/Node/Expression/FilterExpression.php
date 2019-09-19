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
use ToolsetAdvancedExport\Twig\TwigFilter;
class FilterExpression extends \ToolsetAdvancedExport\Twig\Node\Expression\CallExpression
{
    public function __construct(\ToolsetAdvancedExport\Twig_NodeInterface $node, \ToolsetAdvancedExport\Twig\Node\Expression\ConstantExpression $filterName, \ToolsetAdvancedExport\Twig_NodeInterface $arguments, $lineno, $tag = null)
    {
        parent::__construct(['node' => $node, 'filter' => $filterName, 'arguments' => $arguments], [], $lineno, $tag);
    }
    public function compile(\ToolsetAdvancedExport\Twig\Compiler $compiler)
    {
        $name = $this->getNode('filter')->getAttribute('value');
        $filter = $compiler->getEnvironment()->getFilter($name);
        $this->setAttribute('name', $name);
        $this->setAttribute('type', 'filter');
        $this->setAttribute('thing', $filter);
        $this->setAttribute('needs_environment', $filter->needsEnvironment());
        $this->setAttribute('needs_context', $filter->needsContext());
        $this->setAttribute('arguments', $filter->getArguments());
        if ($filter instanceof \ToolsetAdvancedExport\Twig_FilterCallableInterface || $filter instanceof \ToolsetAdvancedExport\Twig\TwigFilter) {
            $this->setAttribute('callable', $filter->getCallable());
        }
        if ($filter instanceof \ToolsetAdvancedExport\Twig\TwigFilter) {
            $this->setAttribute('is_variadic', $filter->isVariadic());
        }
        $this->compileCallable($compiler);
    }
}
\class_alias('ToolsetAdvancedExport\\Twig\\Node\\Expression\\FilterExpression', 'ToolsetAdvancedExport\\Twig_Node_Expression_Filter');
