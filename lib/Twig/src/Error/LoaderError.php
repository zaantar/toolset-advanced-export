<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ToolsetAdvancedExport\Twig\Error;

/**
 * Exception thrown when an error occurs during template loading.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class LoaderError extends \ToolsetAdvancedExport\Twig\Error\Error
{
}
\class_alias('ToolsetAdvancedExport\\Twig\\Error\\LoaderError', 'ToolsetAdvancedExport\\Twig_Error_Loader');
