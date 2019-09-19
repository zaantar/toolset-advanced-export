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
use ToolsetAdvancedExport\Twig\Node\Node;
/**
 * Used to make node visitors compatible with Twig 1.x and 2.x.
 *
 * To be removed in Twig 3.1.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
abstract class AbstractNodeVisitor implements \ToolsetAdvancedExport\Twig\NodeVisitor\NodeVisitorInterface
{
    public final function enterNode(\ToolsetAdvancedExport\Twig_NodeInterface $node, \ToolsetAdvancedExport\Twig\Environment $env)
    {
        if (!$node instanceof \ToolsetAdvancedExport\Twig\Node\Node) {
            throw new \LogicException(\sprintf('%s only supports \\Twig\\Node\\Node instances.', __CLASS__));
        }
        return $this->doEnterNode($node, $env);
    }
    public final function leaveNode(\ToolsetAdvancedExport\Twig_NodeInterface $node, \ToolsetAdvancedExport\Twig\Environment $env)
    {
        if (!$node instanceof \ToolsetAdvancedExport\Twig\Node\Node) {
            throw new \LogicException(\sprintf('%s only supports \\Twig\\Node\\Node instances.', __CLASS__));
        }
        return $this->doLeaveNode($node, $env);
    }
    /**
     * Called before child nodes are visited.
     *
     * @return Node The modified node
     */
    protected abstract function doEnterNode(\ToolsetAdvancedExport\Twig\Node\Node $node, \ToolsetAdvancedExport\Twig\Environment $env);
    /**
     * Called after child nodes are visited.
     *
     * @return Node|false|null The modified node or null if the node must be removed
     */
    protected abstract function doLeaveNode(\ToolsetAdvancedExport\Twig\Node\Node $node, \ToolsetAdvancedExport\Twig\Environment $env);
}
\class_alias('ToolsetAdvancedExport\\Twig\\NodeVisitor\\AbstractNodeVisitor', 'ToolsetAdvancedExport\\Twig_BaseNodeVisitor');
