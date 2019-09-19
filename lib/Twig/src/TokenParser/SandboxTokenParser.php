<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ToolsetAdvancedExport\Twig\TokenParser;

use ToolsetAdvancedExport\Twig\Error\SyntaxError;
use ToolsetAdvancedExport\Twig\Node\IncludeNode;
use ToolsetAdvancedExport\Twig\Node\SandboxNode;
use ToolsetAdvancedExport\Twig\Node\TextNode;
use ToolsetAdvancedExport\Twig\Token;
/**
 * Marks a section of a template as untrusted code that must be evaluated in the sandbox mode.
 *
 *    {% sandbox %}
 *        {% include 'user.html' %}
 *    {% endsandbox %}
 *
 * @see https://twig.symfony.com/doc/api.html#sandbox-extension for details
 *
 * @final
 */
class SandboxTokenParser extends \ToolsetAdvancedExport\Twig\TokenParser\AbstractTokenParser
{
    public function parse(\ToolsetAdvancedExport\Twig\Token $token)
    {
        $stream = $this->parser->getStream();
        $stream->expect(\ToolsetAdvancedExport\Twig\Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse([$this, 'decideBlockEnd'], \true);
        $stream->expect(\ToolsetAdvancedExport\Twig\Token::BLOCK_END_TYPE);
        // in a sandbox tag, only include tags are allowed
        if (!$body instanceof \ToolsetAdvancedExport\Twig\Node\IncludeNode) {
            foreach ($body as $node) {
                if ($node instanceof \ToolsetAdvancedExport\Twig\Node\TextNode && \ctype_space($node->getAttribute('data'))) {
                    continue;
                }
                if (!$node instanceof \ToolsetAdvancedExport\Twig\Node\IncludeNode) {
                    throw new \ToolsetAdvancedExport\Twig\Error\SyntaxError('Only "include" tags are allowed within a "sandbox" section.', $node->getTemplateLine(), $stream->getSourceContext());
                }
            }
        }
        return new \ToolsetAdvancedExport\Twig\Node\SandboxNode($body, $token->getLine(), $this->getTag());
    }
    public function decideBlockEnd(\ToolsetAdvancedExport\Twig\Token $token)
    {
        return $token->test('endsandbox');
    }
    public function getTag()
    {
        return 'sandbox';
    }
}
\class_alias('ToolsetAdvancedExport\\Twig\\TokenParser\\SandboxTokenParser', 'ToolsetAdvancedExport\\Twig_TokenParser_Sandbox');
