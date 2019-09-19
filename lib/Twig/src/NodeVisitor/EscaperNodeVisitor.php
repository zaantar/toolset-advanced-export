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
use ToolsetAdvancedExport\Twig\Node\AutoEscapeNode;
use ToolsetAdvancedExport\Twig\Node\BlockNode;
use ToolsetAdvancedExport\Twig\Node\BlockReferenceNode;
use ToolsetAdvancedExport\Twig\Node\DoNode;
use ToolsetAdvancedExport\Twig\Node\Expression\ConditionalExpression;
use ToolsetAdvancedExport\Twig\Node\Expression\ConstantExpression;
use ToolsetAdvancedExport\Twig\Node\Expression\FilterExpression;
use ToolsetAdvancedExport\Twig\Node\Expression\InlinePrint;
use ToolsetAdvancedExport\Twig\Node\ImportNode;
use ToolsetAdvancedExport\Twig\Node\ModuleNode;
use ToolsetAdvancedExport\Twig\Node\Node;
use ToolsetAdvancedExport\Twig\Node\PrintNode;
use ToolsetAdvancedExport\Twig\NodeTraverser;
/**
 * @final
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class EscaperNodeVisitor extends \ToolsetAdvancedExport\Twig\NodeVisitor\AbstractNodeVisitor
{
    protected $statusStack = [];
    protected $blocks = [];
    protected $safeAnalysis;
    protected $traverser;
    protected $defaultStrategy = \false;
    protected $safeVars = [];
    public function __construct()
    {
        $this->safeAnalysis = new \ToolsetAdvancedExport\Twig\NodeVisitor\SafeAnalysisNodeVisitor();
    }
    protected function doEnterNode(\ToolsetAdvancedExport\Twig\Node\Node $node, \ToolsetAdvancedExport\Twig\Environment $env)
    {
        if ($node instanceof \ToolsetAdvancedExport\Twig\Node\ModuleNode) {
            if ($env->hasExtension('ToolsetAdvancedExport\\Twig\\Extension\\EscaperExtension') && ($defaultStrategy = $env->getExtension('ToolsetAdvancedExport\\Twig\\Extension\\EscaperExtension')->getDefaultStrategy($node->getTemplateName()))) {
                $this->defaultStrategy = $defaultStrategy;
            }
            $this->safeVars = [];
            $this->blocks = [];
        } elseif ($node instanceof \ToolsetAdvancedExport\Twig\Node\AutoEscapeNode) {
            $this->statusStack[] = $node->getAttribute('value');
        } elseif ($node instanceof \ToolsetAdvancedExport\Twig\Node\BlockNode) {
            $this->statusStack[] = isset($this->blocks[$node->getAttribute('name')]) ? $this->blocks[$node->getAttribute('name')] : $this->needEscaping($env);
        } elseif ($node instanceof \ToolsetAdvancedExport\Twig\Node\ImportNode) {
            $this->safeVars[] = $node->getNode('var')->getAttribute('name');
        }
        return $node;
    }
    protected function doLeaveNode(\ToolsetAdvancedExport\Twig\Node\Node $node, \ToolsetAdvancedExport\Twig\Environment $env)
    {
        if ($node instanceof \ToolsetAdvancedExport\Twig\Node\ModuleNode) {
            $this->defaultStrategy = \false;
            $this->safeVars = [];
            $this->blocks = [];
        } elseif ($node instanceof \ToolsetAdvancedExport\Twig\Node\Expression\FilterExpression) {
            return $this->preEscapeFilterNode($node, $env);
        } elseif ($node instanceof \ToolsetAdvancedExport\Twig\Node\PrintNode && \false !== ($type = $this->needEscaping($env))) {
            $expression = $node->getNode('expr');
            if ($expression instanceof \ToolsetAdvancedExport\Twig\Node\Expression\ConditionalExpression && $this->shouldUnwrapConditional($expression, $env, $type)) {
                return new \ToolsetAdvancedExport\Twig\Node\DoNode($this->unwrapConditional($expression, $env, $type), $expression->getTemplateLine());
            }
            return $this->escapePrintNode($node, $env, $type);
        }
        if ($node instanceof \ToolsetAdvancedExport\Twig\Node\AutoEscapeNode || $node instanceof \ToolsetAdvancedExport\Twig\Node\BlockNode) {
            \array_pop($this->statusStack);
        } elseif ($node instanceof \ToolsetAdvancedExport\Twig\Node\BlockReferenceNode) {
            $this->blocks[$node->getAttribute('name')] = $this->needEscaping($env);
        }
        return $node;
    }
    private function shouldUnwrapConditional(\ToolsetAdvancedExport\Twig\Node\Expression\ConditionalExpression $expression, \ToolsetAdvancedExport\Twig\Environment $env, $type)
    {
        $expr2Safe = $this->isSafeFor($type, $expression->getNode('expr2'), $env);
        $expr3Safe = $this->isSafeFor($type, $expression->getNode('expr3'), $env);
        return $expr2Safe !== $expr3Safe;
    }
    private function unwrapConditional(\ToolsetAdvancedExport\Twig\Node\Expression\ConditionalExpression $expression, \ToolsetAdvancedExport\Twig\Environment $env, $type)
    {
        // convert "echo a ? b : c" to "a ? echo b : echo c" recursively
        $expr2 = $expression->getNode('expr2');
        if ($expr2 instanceof \ToolsetAdvancedExport\Twig\Node\Expression\ConditionalExpression && $this->shouldUnwrapConditional($expr2, $env, $type)) {
            $expr2 = $this->unwrapConditional($expr2, $env, $type);
        } else {
            $expr2 = $this->escapeInlinePrintNode(new \ToolsetAdvancedExport\Twig\Node\Expression\InlinePrint($expr2, $expr2->getTemplateLine()), $env, $type);
        }
        $expr3 = $expression->getNode('expr3');
        if ($expr3 instanceof \ToolsetAdvancedExport\Twig\Node\Expression\ConditionalExpression && $this->shouldUnwrapConditional($expr3, $env, $type)) {
            $expr3 = $this->unwrapConditional($expr3, $env, $type);
        } else {
            $expr3 = $this->escapeInlinePrintNode(new \ToolsetAdvancedExport\Twig\Node\Expression\InlinePrint($expr3, $expr3->getTemplateLine()), $env, $type);
        }
        return new \ToolsetAdvancedExport\Twig\Node\Expression\ConditionalExpression($expression->getNode('expr1'), $expr2, $expr3, $expression->getTemplateLine());
    }
    private function escapeInlinePrintNode(\ToolsetAdvancedExport\Twig\Node\Expression\InlinePrint $node, \ToolsetAdvancedExport\Twig\Environment $env, $type)
    {
        $expression = $node->getNode('node');
        if ($this->isSafeFor($type, $expression, $env)) {
            return $node;
        }
        return new \ToolsetAdvancedExport\Twig\Node\Expression\InlinePrint($this->getEscaperFilter($type, $expression), $node->getTemplateLine());
    }
    protected function escapePrintNode(\ToolsetAdvancedExport\Twig\Node\PrintNode $node, \ToolsetAdvancedExport\Twig\Environment $env, $type)
    {
        if (\false === $type) {
            return $node;
        }
        $expression = $node->getNode('expr');
        if ($this->isSafeFor($type, $expression, $env)) {
            return $node;
        }
        $class = \get_class($node);
        return new $class($this->getEscaperFilter($type, $expression), $node->getTemplateLine());
    }
    protected function preEscapeFilterNode(\ToolsetAdvancedExport\Twig\Node\Expression\FilterExpression $filter, \ToolsetAdvancedExport\Twig\Environment $env)
    {
        $name = $filter->getNode('filter')->getAttribute('value');
        $type = $env->getFilter($name)->getPreEscape();
        if (null === $type) {
            return $filter;
        }
        $node = $filter->getNode('node');
        if ($this->isSafeFor($type, $node, $env)) {
            return $filter;
        }
        $filter->setNode('node', $this->getEscaperFilter($type, $node));
        return $filter;
    }
    protected function isSafeFor($type, \ToolsetAdvancedExport\Twig_NodeInterface $expression, $env)
    {
        $safe = $this->safeAnalysis->getSafe($expression);
        if (null === $safe) {
            if (null === $this->traverser) {
                $this->traverser = new \ToolsetAdvancedExport\Twig\NodeTraverser($env, [$this->safeAnalysis]);
            }
            $this->safeAnalysis->setSafeVars($this->safeVars);
            $this->traverser->traverse($expression);
            $safe = $this->safeAnalysis->getSafe($expression);
        }
        return \in_array($type, $safe) || \in_array('all', $safe);
    }
    protected function needEscaping(\ToolsetAdvancedExport\Twig\Environment $env)
    {
        if (\count($this->statusStack)) {
            return $this->statusStack[\count($this->statusStack) - 1];
        }
        return $this->defaultStrategy ? $this->defaultStrategy : \false;
    }
    protected function getEscaperFilter($type, \ToolsetAdvancedExport\Twig_NodeInterface $node)
    {
        $line = $node->getTemplateLine();
        $name = new \ToolsetAdvancedExport\Twig\Node\Expression\ConstantExpression('escape', $line);
        $args = new \ToolsetAdvancedExport\Twig\Node\Node([new \ToolsetAdvancedExport\Twig\Node\Expression\ConstantExpression((string) $type, $line), new \ToolsetAdvancedExport\Twig\Node\Expression\ConstantExpression(null, $line), new \ToolsetAdvancedExport\Twig\Node\Expression\ConstantExpression(\true, $line)]);
        return new \ToolsetAdvancedExport\Twig\Node\Expression\FilterExpression($node, $name, $args, $line);
    }
    public function getPriority()
    {
        return 0;
    }
}
\class_alias('ToolsetAdvancedExport\\Twig\\NodeVisitor\\EscaperNodeVisitor', 'ToolsetAdvancedExport\\Twig_NodeVisitor_Escaper');
