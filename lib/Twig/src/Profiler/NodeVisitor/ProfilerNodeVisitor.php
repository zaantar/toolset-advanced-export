<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ToolsetAdvancedExport\Twig\Profiler\NodeVisitor;

use ToolsetAdvancedExport\Twig\Environment;
use ToolsetAdvancedExport\Twig\Node\BlockNode;
use ToolsetAdvancedExport\Twig\Node\BodyNode;
use ToolsetAdvancedExport\Twig\Node\MacroNode;
use ToolsetAdvancedExport\Twig\Node\ModuleNode;
use ToolsetAdvancedExport\Twig\Node\Node;
use ToolsetAdvancedExport\Twig\NodeVisitor\AbstractNodeVisitor;
use ToolsetAdvancedExport\Twig\Profiler\Node\EnterProfileNode;
use ToolsetAdvancedExport\Twig\Profiler\Node\LeaveProfileNode;
use ToolsetAdvancedExport\Twig\Profiler\Profile;
/**
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @final
 */
class ProfilerNodeVisitor extends \ToolsetAdvancedExport\Twig\NodeVisitor\AbstractNodeVisitor
{
    private $extensionName;
    public function __construct($extensionName)
    {
        $this->extensionName = $extensionName;
    }
    protected function doEnterNode(\ToolsetAdvancedExport\Twig\Node\Node $node, \ToolsetAdvancedExport\Twig\Environment $env)
    {
        return $node;
    }
    protected function doLeaveNode(\ToolsetAdvancedExport\Twig\Node\Node $node, \ToolsetAdvancedExport\Twig\Environment $env)
    {
        if ($node instanceof \ToolsetAdvancedExport\Twig\Node\ModuleNode) {
            $varName = $this->getVarName();
            $node->setNode('display_start', new \ToolsetAdvancedExport\Twig\Node\Node([new \ToolsetAdvancedExport\Twig\Profiler\Node\EnterProfileNode($this->extensionName, \ToolsetAdvancedExport\Twig\Profiler\Profile::TEMPLATE, $node->getTemplateName(), $varName), $node->getNode('display_start')]));
            $node->setNode('display_end', new \ToolsetAdvancedExport\Twig\Node\Node([new \ToolsetAdvancedExport\Twig\Profiler\Node\LeaveProfileNode($varName), $node->getNode('display_end')]));
        } elseif ($node instanceof \ToolsetAdvancedExport\Twig\Node\BlockNode) {
            $varName = $this->getVarName();
            $node->setNode('body', new \ToolsetAdvancedExport\Twig\Node\BodyNode([new \ToolsetAdvancedExport\Twig\Profiler\Node\EnterProfileNode($this->extensionName, \ToolsetAdvancedExport\Twig\Profiler\Profile::BLOCK, $node->getAttribute('name'), $varName), $node->getNode('body'), new \ToolsetAdvancedExport\Twig\Profiler\Node\LeaveProfileNode($varName)]));
        } elseif ($node instanceof \ToolsetAdvancedExport\Twig\Node\MacroNode) {
            $varName = $this->getVarName();
            $node->setNode('body', new \ToolsetAdvancedExport\Twig\Node\BodyNode([new \ToolsetAdvancedExport\Twig\Profiler\Node\EnterProfileNode($this->extensionName, \ToolsetAdvancedExport\Twig\Profiler\Profile::MACRO, $node->getAttribute('name'), $varName), $node->getNode('body'), new \ToolsetAdvancedExport\Twig\Profiler\Node\LeaveProfileNode($varName)]));
        }
        return $node;
    }
    private function getVarName()
    {
        return \sprintf('__internal_%s', \hash('sha256', $this->extensionName));
    }
    public function getPriority()
    {
        return 0;
    }
}
\class_alias('ToolsetAdvancedExport\\Twig\\Profiler\\NodeVisitor\\ProfilerNodeVisitor', 'ToolsetAdvancedExport\\Twig_Profiler_NodeVisitor_Profiler');
