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
use ToolsetAdvancedExport\Twig\Node\Expression\Binary\AndBinary;
use ToolsetAdvancedExport\Twig\Node\Expression\Test\DefinedTest;
use ToolsetAdvancedExport\Twig\Node\Expression\Test\NullTest;
use ToolsetAdvancedExport\Twig\Node\Expression\Unary\NotUnary;
use ToolsetAdvancedExport\Twig\Node\Node;
class NullCoalesceExpression extends \ToolsetAdvancedExport\Twig\Node\Expression\ConditionalExpression
{
    public function __construct(\ToolsetAdvancedExport\Twig_NodeInterface $left, \ToolsetAdvancedExport\Twig_NodeInterface $right, $lineno)
    {
        $test = new \ToolsetAdvancedExport\Twig\Node\Expression\Binary\AndBinary(new \ToolsetAdvancedExport\Twig\Node\Expression\Test\DefinedTest(clone $left, 'defined', new \ToolsetAdvancedExport\Twig\Node\Node(), $left->getTemplateLine()), new \ToolsetAdvancedExport\Twig\Node\Expression\Unary\NotUnary(new \ToolsetAdvancedExport\Twig\Node\Expression\Test\NullTest($left, 'null', new \ToolsetAdvancedExport\Twig\Node\Node(), $left->getTemplateLine()), $left->getTemplateLine()), $left->getTemplateLine());
        parent::__construct($test, $left, $right, $lineno);
    }
    public function compile(\ToolsetAdvancedExport\Twig\Compiler $compiler)
    {
        /*
         * This optimizes only one case. PHP 7 also supports more complex expressions
         * that can return null. So, for instance, if log is defined, log("foo") ?? "..." works,
         * but log($a["foo"]) ?? "..." does not if $a["foo"] is not defined. More advanced
         * cases might be implemented as an optimizer node visitor, but has not been done
         * as benefits are probably not worth the added complexity.
         */
        if (\PHP_VERSION_ID >= 70000 && $this->getNode('expr2') instanceof \ToolsetAdvancedExport\Twig\Node\Expression\NameExpression) {
            $this->getNode('expr2')->setAttribute('always_defined', \true);
            $compiler->raw('((')->subcompile($this->getNode('expr2'))->raw(') ?? (')->subcompile($this->getNode('expr3'))->raw('))');
        } else {
            parent::compile($compiler);
        }
    }
}
\class_alias('ToolsetAdvancedExport\\Twig\\Node\\Expression\\NullCoalesceExpression', 'ToolsetAdvancedExport\\Twig_Node_Expression_NullCoalesce');
