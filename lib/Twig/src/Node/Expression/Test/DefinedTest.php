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
use ToolsetAdvancedExport\Twig\Error\SyntaxError;
use ToolsetAdvancedExport\Twig\Node\Expression\ArrayExpression;
use ToolsetAdvancedExport\Twig\Node\Expression\BlockReferenceExpression;
use ToolsetAdvancedExport\Twig\Node\Expression\ConstantExpression;
use ToolsetAdvancedExport\Twig\Node\Expression\FunctionExpression;
use ToolsetAdvancedExport\Twig\Node\Expression\GetAttrExpression;
use ToolsetAdvancedExport\Twig\Node\Expression\NameExpression;
use ToolsetAdvancedExport\Twig\Node\Expression\TestExpression;
/**
 * Checks if a variable is defined in the current context.
 *
 *    {# defined works with variable names and variable attributes #}
 *    {% if foo is defined %}
 *        {# ... #}
 *    {% endif %}
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class DefinedTest extends \ToolsetAdvancedExport\Twig\Node\Expression\TestExpression
{
    public function __construct(\ToolsetAdvancedExport\Twig_NodeInterface $node, $name, \ToolsetAdvancedExport\Twig_NodeInterface $arguments = null, $lineno)
    {
        if ($node instanceof \ToolsetAdvancedExport\Twig\Node\Expression\NameExpression) {
            $node->setAttribute('is_defined_test', \true);
        } elseif ($node instanceof \ToolsetAdvancedExport\Twig\Node\Expression\GetAttrExpression) {
            $node->setAttribute('is_defined_test', \true);
            $this->changeIgnoreStrictCheck($node);
        } elseif ($node instanceof \ToolsetAdvancedExport\Twig\Node\Expression\BlockReferenceExpression) {
            $node->setAttribute('is_defined_test', \true);
        } elseif ($node instanceof \ToolsetAdvancedExport\Twig\Node\Expression\FunctionExpression && 'constant' === $node->getAttribute('name')) {
            $node->setAttribute('is_defined_test', \true);
        } elseif ($node instanceof \ToolsetAdvancedExport\Twig\Node\Expression\ConstantExpression || $node instanceof \ToolsetAdvancedExport\Twig\Node\Expression\ArrayExpression) {
            $node = new \ToolsetAdvancedExport\Twig\Node\Expression\ConstantExpression(\true, $node->getTemplateLine());
        } else {
            throw new \ToolsetAdvancedExport\Twig\Error\SyntaxError('The "defined" test only works with simple variables.', $lineno);
        }
        parent::__construct($node, $name, $arguments, $lineno);
    }
    protected function changeIgnoreStrictCheck(\ToolsetAdvancedExport\Twig\Node\Expression\GetAttrExpression $node)
    {
        $node->setAttribute('ignore_strict_check', \true);
        if ($node->getNode('node') instanceof \ToolsetAdvancedExport\Twig\Node\Expression\GetAttrExpression) {
            $this->changeIgnoreStrictCheck($node->getNode('node'));
        }
    }
    public function compile(\ToolsetAdvancedExport\Twig\Compiler $compiler)
    {
        $compiler->subcompile($this->getNode('node'));
    }
}
\class_alias('ToolsetAdvancedExport\\Twig\\Node\\Expression\\Test\\DefinedTest', 'ToolsetAdvancedExport\\Twig_Node_Expression_Test_Defined');
