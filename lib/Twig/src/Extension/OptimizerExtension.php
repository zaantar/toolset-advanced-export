<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ToolsetAdvancedExport\Twig\Extension;

use ToolsetAdvancedExport\Twig\NodeVisitor\OptimizerNodeVisitor;
/**
 * @final
 */
class OptimizerExtension extends \ToolsetAdvancedExport\Twig\Extension\AbstractExtension
{
    protected $optimizers;
    public function __construct($optimizers = -1)
    {
        $this->optimizers = $optimizers;
    }
    public function getNodeVisitors()
    {
        return [new \ToolsetAdvancedExport\Twig\NodeVisitor\OptimizerNodeVisitor($this->optimizers)];
    }
    public function getName()
    {
        return 'optimizer';
    }
}
\class_alias('ToolsetAdvancedExport\\Twig\\Extension\\OptimizerExtension', 'ToolsetAdvancedExport\\Twig_Extension_Optimizer');
