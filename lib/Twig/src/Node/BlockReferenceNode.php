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
namespace ToolsetAdvancedExport\Twig\Node;

use ToolsetAdvancedExport\Twig\Compiler;
/**
 * Represents a block call node.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class BlockReferenceNode extends \ToolsetAdvancedExport\Twig\Node\Node implements \ToolsetAdvancedExport\Twig\Node\NodeOutputInterface
{
    public function __construct($name, $lineno, $tag = null)
    {
        parent::__construct([], ['name' => $name], $lineno, $tag);
    }
    public function compile(\ToolsetAdvancedExport\Twig\Compiler $compiler)
    {
        $compiler->addDebugInfo($this)->write(\sprintf("\$this->displayBlock('%s', \$context, \$blocks);\n", $this->getAttribute('name')));
    }
}
\class_alias('ToolsetAdvancedExport\\Twig\\Node\\BlockReferenceNode', 'ToolsetAdvancedExport\\Twig_Node_BlockReference');
