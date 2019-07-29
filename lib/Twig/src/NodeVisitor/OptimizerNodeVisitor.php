<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ToolsetAdvancedExport\Twig\NodeVisitor;

use ToolsetAdvancedExport\Twig\Environment;
use ToolsetAdvancedExport\Twig\Node\BlockReferenceNode;
use ToolsetAdvancedExport\Twig\Node\BodyNode;
use ToolsetAdvancedExport\Twig\Node\Expression\AbstractExpression;
use ToolsetAdvancedExport\Twig\Node\Expression\BlockReferenceExpression;
use ToolsetAdvancedExport\Twig\Node\Expression\ConstantExpression;
use ToolsetAdvancedExport\Twig\Node\Expression\FilterExpression;
use ToolsetAdvancedExport\Twig\Node\Expression\FunctionExpression;
use ToolsetAdvancedExport\Twig\Node\Expression\GetAttrExpression;
use ToolsetAdvancedExport\Twig\Node\Expression\NameExpression;
use ToolsetAdvancedExport\Twig\Node\Expression\ParentExpression;
use ToolsetAdvancedExport\Twig\Node\Expression\TempNameExpression;
use ToolsetAdvancedExport\Twig\Node\ForNode;
use ToolsetAdvancedExport\Twig\Node\IncludeNode;
use ToolsetAdvancedExport\Twig\Node\Node;
use ToolsetAdvancedExport\Twig\Node\PrintNode;
use ToolsetAdvancedExport\Twig\Node\SetTempNode;
/**
 * Tries to optimize the AST.
 *
 * This visitor is always the last registered one.
 *
 * You can configure which optimizations you want to activate via the
 * optimizer mode.
 *
 * @final
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class OptimizerNodeVisitor extends \ToolsetAdvancedExport\Twig\NodeVisitor\AbstractNodeVisitor
{
    const OPTIMIZE_ALL = -1;
    const OPTIMIZE_NONE = 0;
    const OPTIMIZE_FOR = 2;
    const OPTIMIZE_RAW_FILTER = 4;
    const OPTIMIZE_VAR_ACCESS = 8;
    protected $loops = [];
    protected $loopsTargets = [];
    protected $optimizers;
    protected $prependedNodes = [];
    protected $inABody = \false;
    /**
     * @param int $optimizers The optimizer mode
     */
    public function __construct($optimizers = -1)
    {
        if (!\is_int($optimizers) || $optimizers > (self::OPTIMIZE_FOR | self::OPTIMIZE_RAW_FILTER | self::OPTIMIZE_VAR_ACCESS)) {
            throw new \InvalidArgumentException(\sprintf('Optimizer mode "%s" is not valid.', $optimizers));
        }
        $this->optimizers = $optimizers;
    }
    protected function doEnterNode(\ToolsetAdvancedExport\Twig\Node\Node $node, \ToolsetAdvancedExport\Twig\Environment $env)
    {
        if (self::OPTIMIZE_FOR === (self::OPTIMIZE_FOR & $this->optimizers)) {
            $this->enterOptimizeFor($node, $env);
        }
        if (\PHP_VERSION_ID < 50400 && self::OPTIMIZE_VAR_ACCESS === (self::OPTIMIZE_VAR_ACCESS & $this->optimizers) && !$env->isStrictVariables() && !$env->hasExtension('ToolsetAdvancedExport\\Twig\\Extension\\SandboxExtension')) {
            if ($this->inABody) {
                if (!$node instanceof \ToolsetAdvancedExport\Twig\Node\Expression\AbstractExpression) {
                    if ('Twig_Node' !== \get_class($node)) {
                        \array_unshift($this->prependedNodes, []);
                    }
                } else {
                    $node = $this->optimizeVariables($node, $env);
                }
            } elseif ($node instanceof \ToolsetAdvancedExport\Twig\Node\BodyNode) {
                $this->inABody = \true;
            }
        }
        return $node;
    }
    protected function doLeaveNode(\ToolsetAdvancedExport\Twig\Node\Node $node, \ToolsetAdvancedExport\Twig\Environment $env)
    {
        $expression = $node instanceof \ToolsetAdvancedExport\Twig\Node\Expression\AbstractExpression;
        if (self::OPTIMIZE_FOR === (self::OPTIMIZE_FOR & $this->optimizers)) {
            $this->leaveOptimizeFor($node, $env);
        }
        if (self::OPTIMIZE_RAW_FILTER === (self::OPTIMIZE_RAW_FILTER & $this->optimizers)) {
            $node = $this->optimizeRawFilter($node, $env);
        }
        $node = $this->optimizePrintNode($node, $env);
        if (self::OPTIMIZE_VAR_ACCESS === (self::OPTIMIZE_VAR_ACCESS & $this->optimizers) && !$env->isStrictVariables() && !$env->hasExtension('ToolsetAdvancedExport\\Twig\\Extension\\SandboxExtension')) {
            if ($node instanceof \ToolsetAdvancedExport\Twig\Node\BodyNode) {
                $this->inABody = \false;
            } elseif ($this->inABody) {
                if (!$expression && 'Twig_Node' !== \get_class($node) && ($prependedNodes = \array_shift($this->prependedNodes))) {
                    $nodes = [];
                    foreach (\array_unique($prependedNodes) as $name) {
                        $nodes[] = new \ToolsetAdvancedExport\Twig\Node\SetTempNode($name, $node->getTemplateLine());
                    }
                    $nodes[] = $node;
                    $node = new \ToolsetAdvancedExport\Twig\Node\Node($nodes);
                }
            }
        }
        return $node;
    }
    protected function optimizeVariables(\ToolsetAdvancedExport\Twig_NodeInterface $node, \ToolsetAdvancedExport\Twig\Environment $env)
    {
        if ('Twig_Node_Expression_Name' === \get_class($node) && $node->isSimple()) {
            $this->prependedNodes[0][] = $node->getAttribute('name');
            return new \ToolsetAdvancedExport\Twig\Node\Expression\TempNameExpression($node->getAttribute('name'), $node->getTemplateLine());
        }
        return $node;
    }
    /**
     * Optimizes print nodes.
     *
     * It replaces:
     *
     *   * "echo $this->render(Parent)Block()" with "$this->display(Parent)Block()"
     *
     * @return \Twig_NodeInterface
     */
    protected function optimizePrintNode(\ToolsetAdvancedExport\Twig_NodeInterface $node, \ToolsetAdvancedExport\Twig\Environment $env)
    {
        if (!$node instanceof \ToolsetAdvancedExport\Twig\Node\PrintNode) {
            return $node;
        }
        $exprNode = $node->getNode('expr');
        if ($exprNode instanceof \ToolsetAdvancedExport\Twig\Node\Expression\BlockReferenceExpression || $exprNode instanceof \ToolsetAdvancedExport\Twig\Node\Expression\ParentExpression) {
            $exprNode->setAttribute('output', \true);
            return $exprNode;
        }
        return $node;
    }
    /**
     * Removes "raw" filters.
     *
     * @return \Twig_NodeInterface
     */
    protected function optimizeRawFilter(\ToolsetAdvancedExport\Twig_NodeInterface $node, \ToolsetAdvancedExport\Twig\Environment $env)
    {
        if ($node instanceof \ToolsetAdvancedExport\Twig\Node\Expression\FilterExpression && 'raw' == $node->getNode('filter')->getAttribute('value')) {
            return $node->getNode('node');
        }
        return $node;
    }
    /**
     * Optimizes "for" tag by removing the "loop" variable creation whenever possible.
     */
    protected function enterOptimizeFor(\ToolsetAdvancedExport\Twig_NodeInterface $node, \ToolsetAdvancedExport\Twig\Environment $env)
    {
        if ($node instanceof \ToolsetAdvancedExport\Twig\Node\ForNode) {
            // disable the loop variable by default
            $node->setAttribute('with_loop', \false);
            \array_unshift($this->loops, $node);
            \array_unshift($this->loopsTargets, $node->getNode('value_target')->getAttribute('name'));
            \array_unshift($this->loopsTargets, $node->getNode('key_target')->getAttribute('name'));
        } elseif (!$this->loops) {
            // we are outside a loop
            return;
        } elseif ($node instanceof \ToolsetAdvancedExport\Twig\Node\Expression\NameExpression && 'loop' === $node->getAttribute('name')) {
            $node->setAttribute('always_defined', \true);
            $this->addLoopToCurrent();
        } elseif ($node instanceof \ToolsetAdvancedExport\Twig\Node\Expression\NameExpression && \in_array($node->getAttribute('name'), $this->loopsTargets)) {
            $node->setAttribute('always_defined', \true);
        } elseif ($node instanceof \ToolsetAdvancedExport\Twig\Node\BlockReferenceNode || $node instanceof \ToolsetAdvancedExport\Twig\Node\Expression\BlockReferenceExpression) {
            $this->addLoopToCurrent();
        } elseif ($node instanceof \ToolsetAdvancedExport\Twig\Node\IncludeNode && !$node->getAttribute('only')) {
            $this->addLoopToAll();
        } elseif ($node instanceof \ToolsetAdvancedExport\Twig\Node\Expression\FunctionExpression && 'include' === $node->getAttribute('name') && (!$node->getNode('arguments')->hasNode('with_context') || \false !== $node->getNode('arguments')->getNode('with_context')->getAttribute('value'))) {
            $this->addLoopToAll();
        } elseif ($node instanceof \ToolsetAdvancedExport\Twig\Node\Expression\GetAttrExpression && (!$node->getNode('attribute') instanceof \ToolsetAdvancedExport\Twig\Node\Expression\ConstantExpression || 'parent' === $node->getNode('attribute')->getAttribute('value')) && (\true === $this->loops[0]->getAttribute('with_loop') || $node->getNode('node') instanceof \ToolsetAdvancedExport\Twig\Node\Expression\NameExpression && 'loop' === $node->getNode('node')->getAttribute('name'))) {
            $this->addLoopToAll();
        }
    }
    /**
     * Optimizes "for" tag by removing the "loop" variable creation whenever possible.
     */
    protected function leaveOptimizeFor(\ToolsetAdvancedExport\Twig_NodeInterface $node, \ToolsetAdvancedExport\Twig\Environment $env)
    {
        if ($node instanceof \ToolsetAdvancedExport\Twig\Node\ForNode) {
            \array_shift($this->loops);
            \array_shift($this->loopsTargets);
            \array_shift($this->loopsTargets);
        }
    }
    protected function addLoopToCurrent()
    {
        $this->loops[0]->setAttribute('with_loop', \true);
    }
    protected function addLoopToAll()
    {
        foreach ($this->loops as $loop) {
            $loop->setAttribute('with_loop', \true);
        }
    }
    public function getPriority()
    {
        return 255;
    }
}
\class_alias('ToolsetAdvancedExport\\Twig\\NodeVisitor\\OptimizerNodeVisitor', 'ToolsetAdvancedExport\\Twig_NodeVisitor_Optimizer');
