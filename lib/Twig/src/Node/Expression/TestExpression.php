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
use ToolsetAdvancedExport\Twig\TwigTest;
class TestExpression extends \ToolsetAdvancedExport\Twig\Node\Expression\CallExpression
{
    public function __construct(\ToolsetAdvancedExport\Twig_NodeInterface $node, $name, \ToolsetAdvancedExport\Twig_NodeInterface $arguments = null, $lineno)
    {
        $nodes = ['node' => $node];
        if (null !== $arguments) {
            $nodes['arguments'] = $arguments;
        }
        parent::__construct($nodes, ['name' => $name], $lineno);
    }
    public function compile(\ToolsetAdvancedExport\Twig\Compiler $compiler)
    {
        $name = $this->getAttribute('name');
        $test = $compiler->getEnvironment()->getTest($name);
        $this->setAttribute('name', $name);
        $this->setAttribute('type', 'test');
        $this->setAttribute('thing', $test);
        if ($test instanceof \ToolsetAdvancedExport\Twig\TwigTest) {
            $this->setAttribute('arguments', $test->getArguments());
        }
        if ($test instanceof \ToolsetAdvancedExport\Twig_TestCallableInterface || $test instanceof \ToolsetAdvancedExport\Twig\TwigTest) {
            $this->setAttribute('callable', $test->getCallable());
        }
        if ($test instanceof \ToolsetAdvancedExport\Twig\TwigTest) {
            $this->setAttribute('is_variadic', $test->isVariadic());
        }
        $this->compileCallable($compiler);
    }
}
\class_alias('ToolsetAdvancedExport\\Twig\\Node\\Expression\\TestExpression', 'ToolsetAdvancedExport\\Twig_Node_Expression_Test');
