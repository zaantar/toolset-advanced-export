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

use ToolsetAdvancedExport\Twig\TwigFunction;
/**
 * @final
 */
class DebugExtension extends \ToolsetAdvancedExport\Twig\Extension\AbstractExtension
{
    public function getFunctions()
    {
        // dump is safe if var_dump is overridden by xdebug
        $isDumpOutputHtmlSafe = \extension_loaded('xdebug') && (\false === \ini_get('xdebug.overload_var_dump') || \ini_get('xdebug.overload_var_dump')) && (\false === \ini_get('html_errors') || \ini_get('html_errors')) || 'cli' === \PHP_SAPI;
        return [new \ToolsetAdvancedExport\Twig\TwigFunction('dump', 'twig_var_dump', ['is_safe' => $isDumpOutputHtmlSafe ? ['html'] : [], 'needs_context' => \true, 'needs_environment' => \true, 'is_variadic' => \true])];
    }
    public function getName()
    {
        return 'debug';
    }
}
\class_alias('ToolsetAdvancedExport\\Twig\\Extension\\DebugExtension', 'ToolsetAdvancedExport\\Twig_Extension_Debug');
namespace ToolsetAdvancedExport;

use ToolsetAdvancedExport\Twig\Environment;
use ToolsetAdvancedExport\Twig\Template;
use ToolsetAdvancedExport\Twig\TemplateWrapper;
function twig_var_dump(\ToolsetAdvancedExport\Twig\Environment $env, $context, array $vars = [])
{
    if (!$env->isDebug()) {
        return;
    }
    \ob_start();
    if (!$vars) {
        $vars = [];
        foreach ($context as $key => $value) {
            if (!$value instanceof \ToolsetAdvancedExport\Twig\Template && !$value instanceof \ToolsetAdvancedExport\Twig\TemplateWrapper) {
                $vars[$key] = $value;
            }
        }
        \var_dump($vars);
    } else {
        foreach ($vars as $var) {
            \var_dump($var);
        }
    }
    return \ob_get_clean();
}
