<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ToolsetAdvancedExport\Twig\Node;

use ToolsetAdvancedExport\Twig\Compiler;
/**
 * Represents a spaceless node.
 *
 * It removes spaces between HTML tags.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class SpacelessNode extends \ToolsetAdvancedExport\Twig\Node\Node
{
    public function __construct(\ToolsetAdvancedExport\Twig_NodeInterface $body, $lineno, $tag = 'spaceless')
    {
        parent::__construct(['body' => $body], [], $lineno, $tag);
    }
    public function compile(\ToolsetAdvancedExport\Twig\Compiler $compiler)
    {
        $compiler->addDebugInfo($this);
        if ($compiler->getEnvironment()->isDebug()) {
            $compiler->write("ob_start();\n");
        } else {
            $compiler->write("ob_start(function () { return ''; });\n");
        }
        $compiler->subcompile($this->getNode('body'))->write("echo trim(preg_replace('/>\\s+</', '><', ob_get_clean()));\n");
    }
}
\class_alias('ToolsetAdvancedExport\\Twig\\Node\\SpacelessNode', 'ToolsetAdvancedExport\\Twig_Node_Spaceless');
