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
use ToolsetAdvancedExport\Twig\Node\CheckSecurityNode;
use ToolsetAdvancedExport\Twig\Node\CheckToStringNode;
use ToolsetAdvancedExport\Twig\Node\Expression\Binary\ConcatBinary;
use ToolsetAdvancedExport\Twig\Node\Expression\Binary\RangeBinary;
use ToolsetAdvancedExport\Twig\Node\Expression\FilterExpression;
use ToolsetAdvancedExport\Twig\Node\Expression\FunctionExpression;
use ToolsetAdvancedExport\Twig\Node\Expression\GetAttrExpression;
use ToolsetAdvancedExport\Twig\Node\Expression\NameExpression;
use ToolsetAdvancedExport\Twig\Node\ModuleNode;
use ToolsetAdvancedExport\Twig\Node\Node;
use ToolsetAdvancedExport\Twig\Node\PrintNode;
use ToolsetAdvancedExport\Twig\Node\SetNode;
/**
 * @final
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class SandboxNodeVisitor extends \ToolsetAdvancedExport\Twig\NodeVisitor\AbstractNodeVisitor
{
    protected $inAModule = \false;
    protected $tags;
    protected $filters;
    protected $functions;
    private $needsToStringWrap = \false;
    protected function doEnterNode(\ToolsetAdvancedExport\Twig\Node\Node $node, \ToolsetAdvancedExport\Twig\Environment $env)
    {
        if ($node instanceof \ToolsetAdvancedExport\Twig\Node\ModuleNode) {
            $this->inAModule = \true;
            $this->tags = [];
            $this->filters = [];
            $this->functions = [];
            return $node;
        } elseif ($this->inAModule) {
            // look for tags
            if ($node->getNodeTag() && !isset($this->tags[$node->getNodeTag()])) {
                $this->tags[$node->getNodeTag()] = $node;
            }
            // look for filters
            if ($node instanceof \ToolsetAdvancedExport\Twig\Node\Expression\FilterExpression && !isset($this->filters[$node->getNode('filter')->getAttribute('value')])) {
                $this->filters[$node->getNode('filter')->getAttribute('value')] = $node;
            }
            // look for functions
            if ($node instanceof \ToolsetAdvancedExport\Twig\Node\Expression\FunctionExpression && !isset($this->functions[$node->getAttribute('name')])) {
                $this->functions[$node->getAttribute('name')] = $node;
            }
            // the .. operator is equivalent to the range() function
            if ($node instanceof \ToolsetAdvancedExport\Twig\Node\Expression\Binary\RangeBinary && !isset($this->functions['range'])) {
                $this->functions['range'] = $node;
            }
            if ($node instanceof \ToolsetAdvancedExport\Twig\Node\PrintNode) {
                $this->needsToStringWrap = \true;
                $this->wrapNode($node, 'expr');
            }
            if ($node instanceof \ToolsetAdvancedExport\Twig\Node\SetNode && !$node->getAttribute('capture')) {
                $this->needsToStringWrap = \true;
            }
            // wrap outer nodes that can implicitly call __toString()
            if ($this->needsToStringWrap) {
                if ($node instanceof \ToolsetAdvancedExport\Twig\Node\Expression\Binary\ConcatBinary) {
                    $this->wrapNode($node, 'left');
                    $this->wrapNode($node, 'right');
                }
                if ($node instanceof \ToolsetAdvancedExport\Twig\Node\Expression\FilterExpression) {
                    $this->wrapNode($node, 'node');
                    $this->wrapArrayNode($node, 'arguments');
                }
                if ($node instanceof \ToolsetAdvancedExport\Twig\Node\Expression\FunctionExpression) {
                    $this->wrapArrayNode($node, 'arguments');
                }
            }
        }
        return $node;
    }
    protected function doLeaveNode(\ToolsetAdvancedExport\Twig\Node\Node $node, \ToolsetAdvancedExport\Twig\Environment $env)
    {
        if ($node instanceof \ToolsetAdvancedExport\Twig\Node\ModuleNode) {
            $this->inAModule = \false;
            $node->getNode('constructor_end')->setNode('_security_check', new \ToolsetAdvancedExport\Twig\Node\Node([new \ToolsetAdvancedExport\Twig\Node\CheckSecurityNode($this->filters, $this->tags, $this->functions), $node->getNode('display_start')]));
        } elseif ($this->inAModule) {
            if ($node instanceof \ToolsetAdvancedExport\Twig\Node\PrintNode || $node instanceof \ToolsetAdvancedExport\Twig\Node\SetNode) {
                $this->needsToStringWrap = \false;
            }
        }
        return $node;
    }
    private function wrapNode(\ToolsetAdvancedExport\Twig\Node\Node $node, $name)
    {
        $expr = $node->getNode($name);
        if ($expr instanceof \ToolsetAdvancedExport\Twig\Node\Expression\NameExpression || $expr instanceof \ToolsetAdvancedExport\Twig\Node\Expression\GetAttrExpression) {
            $node->setNode($name, new \ToolsetAdvancedExport\Twig\Node\CheckToStringNode($expr));
        }
    }
    private function wrapArrayNode(\ToolsetAdvancedExport\Twig\Node\Node $node, $name)
    {
        $args = $node->getNode($name);
        foreach ($args as $name => $_) {
            $this->wrapNode($args, $name);
        }
    }
    public function getPriority()
    {
        return 0;
    }
}
\class_alias('ToolsetAdvancedExport\\Twig\\NodeVisitor\\SandboxNodeVisitor', 'ToolsetAdvancedExport\\Twig_NodeVisitor_Sandbox');
