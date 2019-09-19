<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ToolsetAdvancedExport\Twig\Loader;

use ToolsetAdvancedExport\Twig\Error\LoaderError;
use ToolsetAdvancedExport\Twig\Source;
/**
 * Loads templates from other loaders.
 *
 * @final
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ChainLoader implements \ToolsetAdvancedExport\Twig\Loader\LoaderInterface, \ToolsetAdvancedExport\Twig\Loader\ExistsLoaderInterface, \ToolsetAdvancedExport\Twig\Loader\SourceContextLoaderInterface
{
    private $hasSourceCache = [];
    protected $loaders = [];
    /**
     * @param LoaderInterface[] $loaders
     */
    public function __construct(array $loaders = [])
    {
        foreach ($loaders as $loader) {
            $this->addLoader($loader);
        }
    }
    public function addLoader(\ToolsetAdvancedExport\Twig\Loader\LoaderInterface $loader)
    {
        $this->loaders[] = $loader;
        $this->hasSourceCache = [];
    }
    /**
     * @return LoaderInterface[]
     */
    public function getLoaders()
    {
        return $this->loaders;
    }
    public function getSource($name)
    {
        @\trigger_error(\sprintf('Calling "getSource" on "%s" is deprecated since 1.27. Use getSourceContext() instead.', \get_class($this)), \E_USER_DEPRECATED);
        $exceptions = [];
        foreach ($this->loaders as $loader) {
            if ($loader instanceof \ToolsetAdvancedExport\Twig\Loader\ExistsLoaderInterface && !$loader->exists($name)) {
                continue;
            }
            try {
                return $loader->getSource($name);
            } catch (\ToolsetAdvancedExport\Twig\Error\LoaderError $e) {
                $exceptions[] = $e->getMessage();
            }
        }
        throw new \ToolsetAdvancedExport\Twig\Error\LoaderError(\sprintf('Template "%s" is not defined%s.', $name, $exceptions ? ' (' . \implode(', ', $exceptions) . ')' : ''));
    }
    public function getSourceContext($name)
    {
        $exceptions = [];
        foreach ($this->loaders as $loader) {
            if ($loader instanceof \ToolsetAdvancedExport\Twig\Loader\ExistsLoaderInterface && !$loader->exists($name)) {
                continue;
            }
            try {
                if ($loader instanceof \ToolsetAdvancedExport\Twig\Loader\SourceContextLoaderInterface) {
                    return $loader->getSourceContext($name);
                }
                return new \ToolsetAdvancedExport\Twig\Source($loader->getSource($name), $name);
            } catch (\ToolsetAdvancedExport\Twig\Error\LoaderError $e) {
                $exceptions[] = $e->getMessage();
            }
        }
        throw new \ToolsetAdvancedExport\Twig\Error\LoaderError(\sprintf('Template "%s" is not defined%s.', $name, $exceptions ? ' (' . \implode(', ', $exceptions) . ')' : ''));
    }
    public function exists($name)
    {
        $name = (string) $name;
        if (isset($this->hasSourceCache[$name])) {
            return $this->hasSourceCache[$name];
        }
        foreach ($this->loaders as $loader) {
            if ($loader instanceof \ToolsetAdvancedExport\Twig\Loader\ExistsLoaderInterface) {
                if ($loader->exists($name)) {
                    return $this->hasSourceCache[$name] = \true;
                }
                continue;
            }
            try {
                if ($loader instanceof \ToolsetAdvancedExport\Twig\Loader\SourceContextLoaderInterface) {
                    $loader->getSourceContext($name);
                } else {
                    $loader->getSource($name);
                }
                return $this->hasSourceCache[$name] = \true;
            } catch (\ToolsetAdvancedExport\Twig\Error\LoaderError $e) {
            }
        }
        return $this->hasSourceCache[$name] = \false;
    }
    public function getCacheKey($name)
    {
        $exceptions = [];
        foreach ($this->loaders as $loader) {
            if ($loader instanceof \ToolsetAdvancedExport\Twig\Loader\ExistsLoaderInterface && !$loader->exists($name)) {
                continue;
            }
            try {
                return $loader->getCacheKey($name);
            } catch (\ToolsetAdvancedExport\Twig\Error\LoaderError $e) {
                $exceptions[] = \get_class($loader) . ': ' . $e->getMessage();
            }
        }
        throw new \ToolsetAdvancedExport\Twig\Error\LoaderError(\sprintf('Template "%s" is not defined%s.', $name, $exceptions ? ' (' . \implode(', ', $exceptions) . ')' : ''));
    }
    public function isFresh($name, $time)
    {
        $exceptions = [];
        foreach ($this->loaders as $loader) {
            if ($loader instanceof \ToolsetAdvancedExport\Twig\Loader\ExistsLoaderInterface && !$loader->exists($name)) {
                continue;
            }
            try {
                return $loader->isFresh($name, $time);
            } catch (\ToolsetAdvancedExport\Twig\Error\LoaderError $e) {
                $exceptions[] = \get_class($loader) . ': ' . $e->getMessage();
            }
        }
        throw new \ToolsetAdvancedExport\Twig\Error\LoaderError(\sprintf('Template "%s" is not defined%s.', $name, $exceptions ? ' (' . \implode(', ', $exceptions) . ')' : ''));
    }
}
\class_alias('ToolsetAdvancedExport\\Twig\\Loader\\ChainLoader', 'ToolsetAdvancedExport\\Twig_Loader_Chain');
