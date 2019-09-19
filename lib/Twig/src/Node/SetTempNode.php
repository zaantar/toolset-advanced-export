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
 * @internal
 */
class SetTempNode extends \ToolsetAdvancedExport\Twig\Node\Node
{
    public function __construct($name, $lineno)
    {
        parent::__construct([], ['name' => $name], $lineno);
    }
    public function compile(\ToolsetAdvancedExport\Twig\Compiler $compiler)
    {
        $name = $this->getAttribute('name');
        $compiler->addDebugInfo($this)->write('if (isset($context[')->string($name)->raw('])) { $_')->raw($name)->raw('_ = $context[')->repr($name)->raw(']; } else { $_')->raw($name)->raw("_ = null; }\n");
    }
}
\class_alias('ToolsetAdvancedExport\\Twig\\Node\\SetTempNode', 'ToolsetAdvancedExport\\Twig_Node_SetTemp');
