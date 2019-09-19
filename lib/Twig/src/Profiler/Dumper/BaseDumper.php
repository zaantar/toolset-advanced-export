<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ToolsetAdvancedExport\Twig\Profiler\Dumper;

use ToolsetAdvancedExport\Twig\Profiler\Profile;
/**
 * @author Fabien Potencier <fabien@symfony.com>
 */
abstract class BaseDumper
{
    private $root;
    public function dump(\ToolsetAdvancedExport\Twig\Profiler\Profile $profile)
    {
        return $this->dumpProfile($profile);
    }
    protected abstract function formatTemplate(\ToolsetAdvancedExport\Twig\Profiler\Profile $profile, $prefix);
    protected abstract function formatNonTemplate(\ToolsetAdvancedExport\Twig\Profiler\Profile $profile, $prefix);
    protected abstract function formatTime(\ToolsetAdvancedExport\Twig\Profiler\Profile $profile, $percent);
    private function dumpProfile(\ToolsetAdvancedExport\Twig\Profiler\Profile $profile, $prefix = '', $sibling = \false)
    {
        if ($profile->isRoot()) {
            $this->root = $profile->getDuration();
            $start = $profile->getName();
        } else {
            if ($profile->isTemplate()) {
                $start = $this->formatTemplate($profile, $prefix);
            } else {
                $start = $this->formatNonTemplate($profile, $prefix);
            }
            $prefix .= $sibling ? '│ ' : '  ';
        }
        $percent = $this->root ? $profile->getDuration() / $this->root * 100 : 0;
        if ($profile->getDuration() * 1000 < 1) {
            $str = $start . "\n";
        } else {
            $str = \sprintf("%s %s\n", $start, $this->formatTime($profile, $percent));
        }
        $nCount = \count($profile->getProfiles());
        foreach ($profile as $i => $p) {
            $str .= $this->dumpProfile($p, $prefix, $i + 1 !== $nCount);
        }
        return $str;
    }
}
\class_alias('ToolsetAdvancedExport\\Twig\\Profiler\\Dumper\\BaseDumper', 'ToolsetAdvancedExport\\Twig_Profiler_Dumper_Base');
