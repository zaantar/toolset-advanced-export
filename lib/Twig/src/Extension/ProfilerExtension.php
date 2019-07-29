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

use ToolsetAdvancedExport\Twig\Profiler\NodeVisitor\ProfilerNodeVisitor;
use ToolsetAdvancedExport\Twig\Profiler\Profile;
class ProfilerExtension extends \ToolsetAdvancedExport\Twig\Extension\AbstractExtension
{
    private $actives = [];
    public function __construct(\ToolsetAdvancedExport\Twig\Profiler\Profile $profile)
    {
        $this->actives[] = $profile;
    }
    public function enter(\ToolsetAdvancedExport\Twig\Profiler\Profile $profile)
    {
        $this->actives[0]->addProfile($profile);
        \array_unshift($this->actives, $profile);
    }
    public function leave(\ToolsetAdvancedExport\Twig\Profiler\Profile $profile)
    {
        $profile->leave();
        \array_shift($this->actives);
        if (1 === \count($this->actives)) {
            $this->actives[0]->leave();
        }
    }
    public function getNodeVisitors()
    {
        return [new \ToolsetAdvancedExport\Twig\Profiler\NodeVisitor\ProfilerNodeVisitor(\get_class($this))];
    }
    public function getName()
    {
        return 'profiler';
    }
}
\class_alias('ToolsetAdvancedExport\\Twig\\Extension\\ProfilerExtension', 'ToolsetAdvancedExport\\Twig_Extension_Profiler');
