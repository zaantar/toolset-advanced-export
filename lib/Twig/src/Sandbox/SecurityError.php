<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ToolsetAdvancedExport\Twig\Sandbox;

use ToolsetAdvancedExport\Twig\Error\Error;
/**
 * Exception thrown when a security error occurs at runtime.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class SecurityError extends \ToolsetAdvancedExport\Twig\Error\Error
{
}
\class_alias('ToolsetAdvancedExport\\Twig\\Sandbox\\SecurityError', 'ToolsetAdvancedExport\\Twig_Sandbox_SecurityError');
