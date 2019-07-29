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
namespace ToolsetAdvancedExport\Twig;

use ToolsetAdvancedExport\Twig\Error\SyntaxError;
use ToolsetAdvancedExport\Twig\Node\Expression\ArrayExpression;
use ToolsetAdvancedExport\Twig\Node\Expression\ArrowFunctionExpression;
use ToolsetAdvancedExport\Twig\Node\Expression\AssignNameExpression;
use ToolsetAdvancedExport\Twig\Node\Expression\Binary\ConcatBinary;
use ToolsetAdvancedExport\Twig\Node\Expression\BlockReferenceExpression;
use ToolsetAdvancedExport\Twig\Node\Expression\ConditionalExpression;
use ToolsetAdvancedExport\Twig\Node\Expression\ConstantExpression;
use ToolsetAdvancedExport\Twig\Node\Expression\GetAttrExpression;
use ToolsetAdvancedExport\Twig\Node\Expression\MethodCallExpression;
use ToolsetAdvancedExport\Twig\Node\Expression\NameExpression;
use ToolsetAdvancedExport\Twig\Node\Expression\ParentExpression;
use ToolsetAdvancedExport\Twig\Node\Expression\Unary\NegUnary;
use ToolsetAdvancedExport\Twig\Node\Expression\Unary\NotUnary;
use ToolsetAdvancedExport\Twig\Node\Expression\Unary\PosUnary;
use ToolsetAdvancedExport\Twig\Node\Node;
/**
 * Parses expressions.
 *
 * This parser implements a "Precedence climbing" algorithm.
 *
 * @see https://www.engr.mun.ca/~theo/Misc/exp_parsing.htm
 * @see https://en.wikipedia.org/wiki/Operator-precedence_parser
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @internal
 */
class ExpressionParser
{
    const OPERATOR_LEFT = 1;
    const OPERATOR_RIGHT = 2;
    protected $parser;
    protected $unaryOperators;
    protected $binaryOperators;
    private $env;
    public function __construct(\ToolsetAdvancedExport\Twig\Parser $parser, $env = null)
    {
        $this->parser = $parser;
        if ($env instanceof \ToolsetAdvancedExport\Twig\Environment) {
            $this->env = $env;
            $this->unaryOperators = $env->getUnaryOperators();
            $this->binaryOperators = $env->getBinaryOperators();
        } else {
            @\trigger_error('Passing the operators as constructor arguments to ' . __METHOD__ . ' is deprecated since version 1.27. Pass the environment instead.', \E_USER_DEPRECATED);
            $this->env = $parser->getEnvironment();
            $this->unaryOperators = \func_get_arg(1);
            $this->binaryOperators = \func_get_arg(2);
        }
    }
    public function parseExpression($precedence = 0, $allowArrow = \false)
    {
        if ($allowArrow && ($arrow = $this->parseArrow())) {
            return $arrow;
        }
        $expr = $this->getPrimary();
        $token = $this->parser->getCurrentToken();
        while ($this->isBinary($token) && $this->binaryOperators[$token->getValue()]['precedence'] >= $precedence) {
            $op = $this->binaryOperators[$token->getValue()];
            $this->parser->getStream()->next();
            if ('is not' === $token->getValue()) {
                $expr = $this->parseNotTestExpression($expr);
            } elseif ('is' === $token->getValue()) {
                $expr = $this->parseTestExpression($expr);
            } elseif (isset($op['callable'])) {
                $expr = \call_user_func($op['callable'], $this->parser, $expr);
            } else {
                $expr1 = $this->parseExpression(self::OPERATOR_LEFT === $op['associativity'] ? $op['precedence'] + 1 : $op['precedence']);
                $class = $op['class'];
                $expr = new $class($expr, $expr1, $token->getLine());
            }
            $token = $this->parser->getCurrentToken();
        }
        if (0 === $precedence) {
            return $this->parseConditionalExpression($expr);
        }
        return $expr;
    }
    /**
     * @return ArrowFunctionExpression|null
     */
    private function parseArrow()
    {
        $stream = $this->parser->getStream();
        // short array syntax (one argument, no parentheses)?
        if ($stream->look(1)->test(\ToolsetAdvancedExport\Twig\Token::ARROW_TYPE)) {
            $line = $stream->getCurrent()->getLine();
            $token = $stream->expect(\ToolsetAdvancedExport\Twig\Token::NAME_TYPE);
            $names = [new \ToolsetAdvancedExport\Twig\Node\Expression\AssignNameExpression($token->getValue(), $token->getLine())];
            $stream->expect(\ToolsetAdvancedExport\Twig\Token::ARROW_TYPE);
            return new \ToolsetAdvancedExport\Twig\Node\Expression\ArrowFunctionExpression($this->parseExpression(0), new \ToolsetAdvancedExport\Twig\Node\Node($names), $line);
        }
        // first, determine if we are parsing an arrow function by finding => (long form)
        $i = 0;
        if (!$stream->look($i)->test(\ToolsetAdvancedExport\Twig\Token::PUNCTUATION_TYPE, '(')) {
            return null;
        }
        ++$i;
        while (\true) {
            // variable name
            ++$i;
            if (!$stream->look($i)->test(\ToolsetAdvancedExport\Twig\Token::PUNCTUATION_TYPE, ',')) {
                break;
            }
            ++$i;
        }
        if (!$stream->look($i)->test(\ToolsetAdvancedExport\Twig\Token::PUNCTUATION_TYPE, ')')) {
            return null;
        }
        ++$i;
        if (!$stream->look($i)->test(\ToolsetAdvancedExport\Twig\Token::ARROW_TYPE)) {
            return null;
        }
        // yes, let's parse it properly
        $token = $stream->expect(\ToolsetAdvancedExport\Twig\Token::PUNCTUATION_TYPE, '(');
        $line = $token->getLine();
        $names = [];
        while (\true) {
            $token = $stream->expect(\ToolsetAdvancedExport\Twig\Token::NAME_TYPE);
            $names[] = new \ToolsetAdvancedExport\Twig\Node\Expression\AssignNameExpression($token->getValue(), $token->getLine());
            if (!$stream->nextIf(\ToolsetAdvancedExport\Twig\Token::PUNCTUATION_TYPE, ',')) {
                break;
            }
        }
        $stream->expect(\ToolsetAdvancedExport\Twig\Token::PUNCTUATION_TYPE, ')');
        $stream->expect(\ToolsetAdvancedExport\Twig\Token::ARROW_TYPE);
        return new \ToolsetAdvancedExport\Twig\Node\Expression\ArrowFunctionExpression($this->parseExpression(0), new \ToolsetAdvancedExport\Twig\Node\Node($names), $line);
    }
    protected function getPrimary()
    {
        $token = $this->parser->getCurrentToken();
        if ($this->isUnary($token)) {
            $operator = $this->unaryOperators[$token->getValue()];
            $this->parser->getStream()->next();
            $expr = $this->parseExpression($operator['precedence']);
            $class = $operator['class'];
            return $this->parsePostfixExpression(new $class($expr, $token->getLine()));
        } elseif ($token->test(\ToolsetAdvancedExport\Twig\Token::PUNCTUATION_TYPE, '(')) {
            $this->parser->getStream()->next();
            $expr = $this->parseExpression();
            $this->parser->getStream()->expect(\ToolsetAdvancedExport\Twig\Token::PUNCTUATION_TYPE, ')', 'An opened parenthesis is not properly closed');
            return $this->parsePostfixExpression($expr);
        }
        return $this->parsePrimaryExpression();
    }
    protected function parseConditionalExpression($expr)
    {
        while ($this->parser->getStream()->nextIf(\ToolsetAdvancedExport\Twig\Token::PUNCTUATION_TYPE, '?')) {
            if (!$this->parser->getStream()->nextIf(\ToolsetAdvancedExport\Twig\Token::PUNCTUATION_TYPE, ':')) {
                $expr2 = $this->parseExpression();
                if ($this->parser->getStream()->nextIf(\ToolsetAdvancedExport\Twig\Token::PUNCTUATION_TYPE, ':')) {
                    $expr3 = $this->parseExpression();
                } else {
                    $expr3 = new \ToolsetAdvancedExport\Twig\Node\Expression\ConstantExpression('', $this->parser->getCurrentToken()->getLine());
                }
            } else {
                $expr2 = $expr;
                $expr3 = $this->parseExpression();
            }
            $expr = new \ToolsetAdvancedExport\Twig\Node\Expression\ConditionalExpression($expr, $expr2, $expr3, $this->parser->getCurrentToken()->getLine());
        }
        return $expr;
    }
    protected function isUnary(\ToolsetAdvancedExport\Twig\Token $token)
    {
        return $token->test(\ToolsetAdvancedExport\Twig\Token::OPERATOR_TYPE) && isset($this->unaryOperators[$token->getValue()]);
    }
    protected function isBinary(\ToolsetAdvancedExport\Twig\Token $token)
    {
        return $token->test(\ToolsetAdvancedExport\Twig\Token::OPERATOR_TYPE) && isset($this->binaryOperators[$token->getValue()]);
    }
    public function parsePrimaryExpression()
    {
        $token = $this->parser->getCurrentToken();
        switch ($token->getType()) {
            case \ToolsetAdvancedExport\Twig\Token::NAME_TYPE:
                $this->parser->getStream()->next();
                switch ($token->getValue()) {
                    case 'true':
                    case 'TRUE':
                        $node = new \ToolsetAdvancedExport\Twig\Node\Expression\ConstantExpression(\true, $token->getLine());
                        break;
                    case 'false':
                    case 'FALSE':
                        $node = new \ToolsetAdvancedExport\Twig\Node\Expression\ConstantExpression(\false, $token->getLine());
                        break;
                    case 'none':
                    case 'NONE':
                    case 'null':
                    case 'NULL':
                        $node = new \ToolsetAdvancedExport\Twig\Node\Expression\ConstantExpression(null, $token->getLine());
                        break;
                    default:
                        if ('(' === $this->parser->getCurrentToken()->getValue()) {
                            $node = $this->getFunctionNode($token->getValue(), $token->getLine());
                        } else {
                            $node = new \ToolsetAdvancedExport\Twig\Node\Expression\NameExpression($token->getValue(), $token->getLine());
                        }
                }
                break;
            case \ToolsetAdvancedExport\Twig\Token::NUMBER_TYPE:
                $this->parser->getStream()->next();
                $node = new \ToolsetAdvancedExport\Twig\Node\Expression\ConstantExpression($token->getValue(), $token->getLine());
                break;
            case \ToolsetAdvancedExport\Twig\Token::STRING_TYPE:
            case \ToolsetAdvancedExport\Twig\Token::INTERPOLATION_START_TYPE:
                $node = $this->parseStringExpression();
                break;
            case \ToolsetAdvancedExport\Twig\Token::OPERATOR_TYPE:
                if (\preg_match(\ToolsetAdvancedExport\Twig\Lexer::REGEX_NAME, $token->getValue(), $matches) && $matches[0] == $token->getValue()) {
                    // in this context, string operators are variable names
                    $this->parser->getStream()->next();
                    $node = new \ToolsetAdvancedExport\Twig\Node\Expression\NameExpression($token->getValue(), $token->getLine());
                    break;
                } elseif (isset($this->unaryOperators[$token->getValue()])) {
                    $class = $this->unaryOperators[$token->getValue()]['class'];
                    $ref = new \ReflectionClass($class);
                    $negClass = 'ToolsetAdvancedExport\\Twig\\Node\\Expression\\Unary\\NegUnary';
                    $posClass = 'ToolsetAdvancedExport\\Twig\\Node\\Expression\\Unary\\PosUnary';
                    if (!(\in_array($ref->getName(), [$negClass, $posClass, 'Twig_Node_Expression_Unary_Neg', 'Twig_Node_Expression_Unary_Pos']) || $ref->isSubclassOf($negClass) || $ref->isSubclassOf($posClass) || $ref->isSubclassOf('Twig_Node_Expression_Unary_Neg') || $ref->isSubclassOf('Twig_Node_Expression_Unary_Pos'))) {
                        throw new \ToolsetAdvancedExport\Twig\Error\SyntaxError(\sprintf('Unexpected unary operator "%s".', $token->getValue()), $token->getLine(), $this->parser->getStream()->getSourceContext());
                    }
                    $this->parser->getStream()->next();
                    $expr = $this->parsePrimaryExpression();
                    $node = new $class($expr, $token->getLine());
                    break;
                }
            // no break
            default:
                if ($token->test(\ToolsetAdvancedExport\Twig\Token::PUNCTUATION_TYPE, '[')) {
                    $node = $this->parseArrayExpression();
                } elseif ($token->test(\ToolsetAdvancedExport\Twig\Token::PUNCTUATION_TYPE, '{')) {
                    $node = $this->parseHashExpression();
                } elseif ($token->test(\ToolsetAdvancedExport\Twig\Token::OPERATOR_TYPE, '=') && ('==' === $this->parser->getStream()->look(-1)->getValue() || '!=' === $this->parser->getStream()->look(-1)->getValue())) {
                    throw new \ToolsetAdvancedExport\Twig\Error\SyntaxError(\sprintf('Unexpected operator of value "%s". Did you try to use "===" or "!==" for strict comparison? Use "is same as(value)" instead.', $token->getValue()), $token->getLine(), $this->parser->getStream()->getSourceContext());
                } else {
                    throw new \ToolsetAdvancedExport\Twig\Error\SyntaxError(\sprintf('Unexpected token "%s" of value "%s".', \ToolsetAdvancedExport\Twig\Token::typeToEnglish($token->getType()), $token->getValue()), $token->getLine(), $this->parser->getStream()->getSourceContext());
                }
        }
        return $this->parsePostfixExpression($node);
    }
    public function parseStringExpression()
    {
        $stream = $this->parser->getStream();
        $nodes = [];
        // a string cannot be followed by another string in a single expression
        $nextCanBeString = \true;
        while (\true) {
            if ($nextCanBeString && ($token = $stream->nextIf(\ToolsetAdvancedExport\Twig\Token::STRING_TYPE))) {
                $nodes[] = new \ToolsetAdvancedExport\Twig\Node\Expression\ConstantExpression($token->getValue(), $token->getLine());
                $nextCanBeString = \false;
            } elseif ($stream->nextIf(\ToolsetAdvancedExport\Twig\Token::INTERPOLATION_START_TYPE)) {
                $nodes[] = $this->parseExpression();
                $stream->expect(\ToolsetAdvancedExport\Twig\Token::INTERPOLATION_END_TYPE);
                $nextCanBeString = \true;
            } else {
                break;
            }
        }
        $expr = \array_shift($nodes);
        foreach ($nodes as $node) {
            $expr = new \ToolsetAdvancedExport\Twig\Node\Expression\Binary\ConcatBinary($expr, $node, $node->getTemplateLine());
        }
        return $expr;
    }
    public function parseArrayExpression()
    {
        $stream = $this->parser->getStream();
        $stream->expect(\ToolsetAdvancedExport\Twig\Token::PUNCTUATION_TYPE, '[', 'An array element was expected');
        $node = new \ToolsetAdvancedExport\Twig\Node\Expression\ArrayExpression([], $stream->getCurrent()->getLine());
        $first = \true;
        while (!$stream->test(\ToolsetAdvancedExport\Twig\Token::PUNCTUATION_TYPE, ']')) {
            if (!$first) {
                $stream->expect(\ToolsetAdvancedExport\Twig\Token::PUNCTUATION_TYPE, ',', 'An array element must be followed by a comma');
                // trailing ,?
                if ($stream->test(\ToolsetAdvancedExport\Twig\Token::PUNCTUATION_TYPE, ']')) {
                    break;
                }
            }
            $first = \false;
            $node->addElement($this->parseExpression());
        }
        $stream->expect(\ToolsetAdvancedExport\Twig\Token::PUNCTUATION_TYPE, ']', 'An opened array is not properly closed');
        return $node;
    }
    public function parseHashExpression()
    {
        $stream = $this->parser->getStream();
        $stream->expect(\ToolsetAdvancedExport\Twig\Token::PUNCTUATION_TYPE, '{', 'A hash element was expected');
        $node = new \ToolsetAdvancedExport\Twig\Node\Expression\ArrayExpression([], $stream->getCurrent()->getLine());
        $first = \true;
        while (!$stream->test(\ToolsetAdvancedExport\Twig\Token::PUNCTUATION_TYPE, '}')) {
            if (!$first) {
                $stream->expect(\ToolsetAdvancedExport\Twig\Token::PUNCTUATION_TYPE, ',', 'A hash value must be followed by a comma');
                // trailing ,?
                if ($stream->test(\ToolsetAdvancedExport\Twig\Token::PUNCTUATION_TYPE, '}')) {
                    break;
                }
            }
            $first = \false;
            // a hash key can be:
            //
            //  * a number -- 12
            //  * a string -- 'a'
            //  * a name, which is equivalent to a string -- a
            //  * an expression, which must be enclosed in parentheses -- (1 + 2)
            if (($token = $stream->nextIf(\ToolsetAdvancedExport\Twig\Token::STRING_TYPE)) || ($token = $stream->nextIf(\ToolsetAdvancedExport\Twig\Token::NAME_TYPE)) || ($token = $stream->nextIf(\ToolsetAdvancedExport\Twig\Token::NUMBER_TYPE))) {
                $key = new \ToolsetAdvancedExport\Twig\Node\Expression\ConstantExpression($token->getValue(), $token->getLine());
            } elseif ($stream->test(\ToolsetAdvancedExport\Twig\Token::PUNCTUATION_TYPE, '(')) {
                $key = $this->parseExpression();
            } else {
                $current = $stream->getCurrent();
                throw new \ToolsetAdvancedExport\Twig\Error\SyntaxError(\sprintf('A hash key must be a quoted string, a number, a name, or an expression enclosed in parentheses (unexpected token "%s" of value "%s".', \ToolsetAdvancedExport\Twig\Token::typeToEnglish($current->getType()), $current->getValue()), $current->getLine(), $stream->getSourceContext());
            }
            $stream->expect(\ToolsetAdvancedExport\Twig\Token::PUNCTUATION_TYPE, ':', 'A hash key must be followed by a colon (:)');
            $value = $this->parseExpression();
            $node->addElement($value, $key);
        }
        $stream->expect(\ToolsetAdvancedExport\Twig\Token::PUNCTUATION_TYPE, '}', 'An opened hash is not properly closed');
        return $node;
    }
    public function parsePostfixExpression($node)
    {
        while (\true) {
            $token = $this->parser->getCurrentToken();
            if (\ToolsetAdvancedExport\Twig\Token::PUNCTUATION_TYPE == $token->getType()) {
                if ('.' == $token->getValue() || '[' == $token->getValue()) {
                    $node = $this->parseSubscriptExpression($node);
                } elseif ('|' == $token->getValue()) {
                    $node = $this->parseFilterExpression($node);
                } else {
                    break;
                }
            } else {
                break;
            }
        }
        return $node;
    }
    public function getFunctionNode($name, $line)
    {
        switch ($name) {
            case 'parent':
                $this->parseArguments();
                if (!\count($this->parser->getBlockStack())) {
                    throw new \ToolsetAdvancedExport\Twig\Error\SyntaxError('Calling "parent" outside a block is forbidden.', $line, $this->parser->getStream()->getSourceContext());
                }
                if (!$this->parser->getParent() && !$this->parser->hasTraits()) {
                    throw new \ToolsetAdvancedExport\Twig\Error\SyntaxError('Calling "parent" on a template that does not extend nor "use" another template is forbidden.', $line, $this->parser->getStream()->getSourceContext());
                }
                return new \ToolsetAdvancedExport\Twig\Node\Expression\ParentExpression($this->parser->peekBlockStack(), $line);
            case 'block':
                $args = $this->parseArguments();
                if (\count($args) < 1) {
                    throw new \ToolsetAdvancedExport\Twig\Error\SyntaxError('The "block" function takes one argument (the block name).', $line, $this->parser->getStream()->getSourceContext());
                }
                return new \ToolsetAdvancedExport\Twig\Node\Expression\BlockReferenceExpression($args->getNode(0), \count($args) > 1 ? $args->getNode(1) : null, $line);
            case 'attribute':
                $args = $this->parseArguments();
                if (\count($args) < 2) {
                    throw new \ToolsetAdvancedExport\Twig\Error\SyntaxError('The "attribute" function takes at least two arguments (the variable and the attributes).', $line, $this->parser->getStream()->getSourceContext());
                }
                return new \ToolsetAdvancedExport\Twig\Node\Expression\GetAttrExpression($args->getNode(0), $args->getNode(1), \count($args) > 2 ? $args->getNode(2) : null, \ToolsetAdvancedExport\Twig\Template::ANY_CALL, $line);
            default:
                if (null !== ($alias = $this->parser->getImportedSymbol('function', $name))) {
                    $arguments = new \ToolsetAdvancedExport\Twig\Node\Expression\ArrayExpression([], $line);
                    foreach ($this->parseArguments() as $n) {
                        $arguments->addElement($n);
                    }
                    $node = new \ToolsetAdvancedExport\Twig\Node\Expression\MethodCallExpression($alias['node'], $alias['name'], $arguments, $line);
                    $node->setAttribute('safe', \true);
                    return $node;
                }
                $args = $this->parseArguments(\true);
                $class = $this->getFunctionNodeClass($name, $line);
                return new $class($name, $args, $line);
        }
    }
    public function parseSubscriptExpression($node)
    {
        $stream = $this->parser->getStream();
        $token = $stream->next();
        $lineno = $token->getLine();
        $arguments = new \ToolsetAdvancedExport\Twig\Node\Expression\ArrayExpression([], $lineno);
        $type = \ToolsetAdvancedExport\Twig\Template::ANY_CALL;
        if ('.' == $token->getValue()) {
            $token = $stream->next();
            if (\ToolsetAdvancedExport\Twig\Token::NAME_TYPE == $token->getType() || \ToolsetAdvancedExport\Twig\Token::NUMBER_TYPE == $token->getType() || \ToolsetAdvancedExport\Twig\Token::OPERATOR_TYPE == $token->getType() && \preg_match(\ToolsetAdvancedExport\Twig\Lexer::REGEX_NAME, $token->getValue())) {
                $arg = new \ToolsetAdvancedExport\Twig\Node\Expression\ConstantExpression($token->getValue(), $lineno);
                if ($stream->test(\ToolsetAdvancedExport\Twig\Token::PUNCTUATION_TYPE, '(')) {
                    $type = \ToolsetAdvancedExport\Twig\Template::METHOD_CALL;
                    foreach ($this->parseArguments() as $n) {
                        $arguments->addElement($n);
                    }
                }
            } else {
                throw new \ToolsetAdvancedExport\Twig\Error\SyntaxError('Expected name or number.', $lineno, $stream->getSourceContext());
            }
            if ($node instanceof \ToolsetAdvancedExport\Twig\Node\Expression\NameExpression && null !== $this->parser->getImportedSymbol('template', $node->getAttribute('name'))) {
                if (!$arg instanceof \ToolsetAdvancedExport\Twig\Node\Expression\ConstantExpression) {
                    throw new \ToolsetAdvancedExport\Twig\Error\SyntaxError(\sprintf('Dynamic macro names are not supported (called on "%s").', $node->getAttribute('name')), $token->getLine(), $stream->getSourceContext());
                }
                $name = $arg->getAttribute('value');
                if ($this->parser->isReservedMacroName($name)) {
                    throw new \ToolsetAdvancedExport\Twig\Error\SyntaxError(\sprintf('"%s" cannot be called as macro as it is a reserved keyword.', $name), $token->getLine(), $stream->getSourceContext());
                }
                $node = new \ToolsetAdvancedExport\Twig\Node\Expression\MethodCallExpression($node, 'get' . $name, $arguments, $lineno);
                $node->setAttribute('safe', \true);
                return $node;
            }
        } else {
            $type = \ToolsetAdvancedExport\Twig\Template::ARRAY_CALL;
            // slice?
            $slice = \false;
            if ($stream->test(\ToolsetAdvancedExport\Twig\Token::PUNCTUATION_TYPE, ':')) {
                $slice = \true;
                $arg = new \ToolsetAdvancedExport\Twig\Node\Expression\ConstantExpression(0, $token->getLine());
            } else {
                $arg = $this->parseExpression();
            }
            if ($stream->nextIf(\ToolsetAdvancedExport\Twig\Token::PUNCTUATION_TYPE, ':')) {
                $slice = \true;
            }
            if ($slice) {
                if ($stream->test(\ToolsetAdvancedExport\Twig\Token::PUNCTUATION_TYPE, ']')) {
                    $length = new \ToolsetAdvancedExport\Twig\Node\Expression\ConstantExpression(null, $token->getLine());
                } else {
                    $length = $this->parseExpression();
                }
                $class = $this->getFilterNodeClass('slice', $token->getLine());
                $arguments = new \ToolsetAdvancedExport\Twig\Node\Node([$arg, $length]);
                $filter = new $class($node, new \ToolsetAdvancedExport\Twig\Node\Expression\ConstantExpression('slice', $token->getLine()), $arguments, $token->getLine());
                $stream->expect(\ToolsetAdvancedExport\Twig\Token::PUNCTUATION_TYPE, ']');
                return $filter;
            }
            $stream->expect(\ToolsetAdvancedExport\Twig\Token::PUNCTUATION_TYPE, ']');
        }
        return new \ToolsetAdvancedExport\Twig\Node\Expression\GetAttrExpression($node, $arg, $arguments, $type, $lineno);
    }
    public function parseFilterExpression($node)
    {
        $this->parser->getStream()->next();
        return $this->parseFilterExpressionRaw($node);
    }
    public function parseFilterExpressionRaw($node, $tag = null)
    {
        while (\true) {
            $token = $this->parser->getStream()->expect(\ToolsetAdvancedExport\Twig\Token::NAME_TYPE);
            $name = new \ToolsetAdvancedExport\Twig\Node\Expression\ConstantExpression($token->getValue(), $token->getLine());
            if (!$this->parser->getStream()->test(\ToolsetAdvancedExport\Twig\Token::PUNCTUATION_TYPE, '(')) {
                $arguments = new \ToolsetAdvancedExport\Twig\Node\Node();
            } else {
                $arguments = $this->parseArguments(\true, \false, \true);
            }
            $class = $this->getFilterNodeClass($name->getAttribute('value'), $token->getLine());
            $node = new $class($node, $name, $arguments, $token->getLine(), $tag);
            if (!$this->parser->getStream()->test(\ToolsetAdvancedExport\Twig\Token::PUNCTUATION_TYPE, '|')) {
                break;
            }
            $this->parser->getStream()->next();
        }
        return $node;
    }
    /**
     * Parses arguments.
     *
     * @param bool $namedArguments Whether to allow named arguments or not
     * @param bool $definition     Whether we are parsing arguments for a function definition
     *
     * @return Node
     *
     * @throws SyntaxError
     */
    public function parseArguments($namedArguments = \false, $definition = \false, $allowArrow = \false)
    {
        $args = [];
        $stream = $this->parser->getStream();
        $stream->expect(\ToolsetAdvancedExport\Twig\Token::PUNCTUATION_TYPE, '(', 'A list of arguments must begin with an opening parenthesis');
        while (!$stream->test(\ToolsetAdvancedExport\Twig\Token::PUNCTUATION_TYPE, ')')) {
            if (!empty($args)) {
                $stream->expect(\ToolsetAdvancedExport\Twig\Token::PUNCTUATION_TYPE, ',', 'Arguments must be separated by a comma');
            }
            if ($definition) {
                $token = $stream->expect(\ToolsetAdvancedExport\Twig\Token::NAME_TYPE, null, 'An argument must be a name');
                $value = new \ToolsetAdvancedExport\Twig\Node\Expression\NameExpression($token->getValue(), $this->parser->getCurrentToken()->getLine());
            } else {
                $value = $this->parseExpression(0, $allowArrow);
            }
            $name = null;
            if ($namedArguments && ($token = $stream->nextIf(\ToolsetAdvancedExport\Twig\Token::OPERATOR_TYPE, '='))) {
                if (!$value instanceof \ToolsetAdvancedExport\Twig\Node\Expression\NameExpression) {
                    throw new \ToolsetAdvancedExport\Twig\Error\SyntaxError(\sprintf('A parameter name must be a string, "%s" given.', \get_class($value)), $token->getLine(), $stream->getSourceContext());
                }
                $name = $value->getAttribute('name');
                if ($definition) {
                    $value = $this->parsePrimaryExpression();
                    if (!$this->checkConstantExpression($value)) {
                        throw new \ToolsetAdvancedExport\Twig\Error\SyntaxError(\sprintf('A default value for an argument must be a constant (a boolean, a string, a number, or an array).'), $token->getLine(), $stream->getSourceContext());
                    }
                } else {
                    $value = $this->parseExpression(0, $allowArrow);
                }
            }
            if ($definition) {
                if (null === $name) {
                    $name = $value->getAttribute('name');
                    $value = new \ToolsetAdvancedExport\Twig\Node\Expression\ConstantExpression(null, $this->parser->getCurrentToken()->getLine());
                }
                $args[$name] = $value;
            } else {
                if (null === $name) {
                    $args[] = $value;
                } else {
                    $args[$name] = $value;
                }
            }
        }
        $stream->expect(\ToolsetAdvancedExport\Twig\Token::PUNCTUATION_TYPE, ')', 'A list of arguments must be closed by a parenthesis');
        return new \ToolsetAdvancedExport\Twig\Node\Node($args);
    }
    public function parseAssignmentExpression()
    {
        $stream = $this->parser->getStream();
        $targets = [];
        while (\true) {
            $token = $this->parser->getCurrentToken();
            if ($stream->test(\ToolsetAdvancedExport\Twig\Token::OPERATOR_TYPE) && \preg_match(\ToolsetAdvancedExport\Twig\Lexer::REGEX_NAME, $token->getValue())) {
                // in this context, string operators are variable names
                $this->parser->getStream()->next();
            } else {
                $stream->expect(\ToolsetAdvancedExport\Twig\Token::NAME_TYPE, null, 'Only variables can be assigned to');
            }
            $value = $token->getValue();
            if (\in_array(\strtolower($value), ['true', 'false', 'none', 'null'])) {
                throw new \ToolsetAdvancedExport\Twig\Error\SyntaxError(\sprintf('You cannot assign a value to "%s".', $value), $token->getLine(), $stream->getSourceContext());
            }
            $targets[] = new \ToolsetAdvancedExport\Twig\Node\Expression\AssignNameExpression($value, $token->getLine());
            if (!$stream->nextIf(\ToolsetAdvancedExport\Twig\Token::PUNCTUATION_TYPE, ',')) {
                break;
            }
        }
        return new \ToolsetAdvancedExport\Twig\Node\Node($targets);
    }
    public function parseMultitargetExpression()
    {
        $targets = [];
        while (\true) {
            $targets[] = $this->parseExpression();
            if (!$this->parser->getStream()->nextIf(\ToolsetAdvancedExport\Twig\Token::PUNCTUATION_TYPE, ',')) {
                break;
            }
        }
        return new \ToolsetAdvancedExport\Twig\Node\Node($targets);
    }
    private function parseNotTestExpression(\ToolsetAdvancedExport\Twig_NodeInterface $node)
    {
        return new \ToolsetAdvancedExport\Twig\Node\Expression\Unary\NotUnary($this->parseTestExpression($node), $this->parser->getCurrentToken()->getLine());
    }
    private function parseTestExpression(\ToolsetAdvancedExport\Twig_NodeInterface $node)
    {
        $stream = $this->parser->getStream();
        list($name, $test) = $this->getTest($node->getTemplateLine());
        $class = $this->getTestNodeClass($test);
        $arguments = null;
        if ($stream->test(\ToolsetAdvancedExport\Twig\Token::PUNCTUATION_TYPE, '(')) {
            $arguments = $this->parseArguments(\true);
        }
        return new $class($node, $name, $arguments, $this->parser->getCurrentToken()->getLine());
    }
    private function getTest($line)
    {
        $stream = $this->parser->getStream();
        $name = $stream->expect(\ToolsetAdvancedExport\Twig\Token::NAME_TYPE)->getValue();
        if ($test = $this->env->getTest($name)) {
            return [$name, $test];
        }
        if ($stream->test(\ToolsetAdvancedExport\Twig\Token::NAME_TYPE)) {
            // try 2-words tests
            $name = $name . ' ' . $this->parser->getCurrentToken()->getValue();
            if ($test = $this->env->getTest($name)) {
                $stream->next();
                return [$name, $test];
            }
        }
        $e = new \ToolsetAdvancedExport\Twig\Error\SyntaxError(\sprintf('Unknown "%s" test.', $name), $line, $stream->getSourceContext());
        $e->addSuggestions($name, \array_keys($this->env->getTests()));
        throw $e;
    }
    private function getTestNodeClass($test)
    {
        if ($test instanceof \ToolsetAdvancedExport\Twig\TwigTest && $test->isDeprecated()) {
            $stream = $this->parser->getStream();
            $message = \sprintf('Twig Test "%s" is deprecated', $test->getName());
            if (!\is_bool($test->getDeprecatedVersion())) {
                $message .= \sprintf(' since version %s', $test->getDeprecatedVersion());
            }
            if ($test->getAlternative()) {
                $message .= \sprintf('. Use "%s" instead', $test->getAlternative());
            }
            $src = $stream->getSourceContext();
            $message .= \sprintf(' in %s at line %d.', $src->getPath() ? $src->getPath() : $src->getName(), $stream->getCurrent()->getLine());
            @\trigger_error($message, \E_USER_DEPRECATED);
        }
        if ($test instanceof \ToolsetAdvancedExport\Twig\TwigTest) {
            return $test->getNodeClass();
        }
        return $test instanceof \ToolsetAdvancedExport\Twig_Test_Node ? $test->getClass() : 'Twig\\Node\\Expression\\TestExpression';
    }
    protected function getFunctionNodeClass($name, $line)
    {
        if (\false === ($function = $this->env->getFunction($name))) {
            $e = new \ToolsetAdvancedExport\Twig\Error\SyntaxError(\sprintf('Unknown "%s" function.', $name), $line, $this->parser->getStream()->getSourceContext());
            $e->addSuggestions($name, \array_keys($this->env->getFunctions()));
            throw $e;
        }
        if ($function instanceof \ToolsetAdvancedExport\Twig\TwigFunction && $function->isDeprecated()) {
            $message = \sprintf('Twig Function "%s" is deprecated', $function->getName());
            if (!\is_bool($function->getDeprecatedVersion())) {
                $message .= \sprintf(' since version %s', $function->getDeprecatedVersion());
            }
            if ($function->getAlternative()) {
                $message .= \sprintf('. Use "%s" instead', $function->getAlternative());
            }
            $src = $this->parser->getStream()->getSourceContext();
            $message .= \sprintf(' in %s at line %d.', $src->getPath() ? $src->getPath() : $src->getName(), $line);
            @\trigger_error($message, \E_USER_DEPRECATED);
        }
        if ($function instanceof \ToolsetAdvancedExport\Twig\TwigFunction) {
            return $function->getNodeClass();
        }
        return $function instanceof \ToolsetAdvancedExport\Twig_Function_Node ? $function->getClass() : 'Twig\\Node\\Expression\\FunctionExpression';
    }
    protected function getFilterNodeClass($name, $line)
    {
        if (\false === ($filter = $this->env->getFilter($name))) {
            $e = new \ToolsetAdvancedExport\Twig\Error\SyntaxError(\sprintf('Unknown "%s" filter.', $name), $line, $this->parser->getStream()->getSourceContext());
            $e->addSuggestions($name, \array_keys($this->env->getFilters()));
            throw $e;
        }
        if ($filter instanceof \ToolsetAdvancedExport\Twig\TwigFilter && $filter->isDeprecated()) {
            $message = \sprintf('Twig Filter "%s" is deprecated', $filter->getName());
            if (!\is_bool($filter->getDeprecatedVersion())) {
                $message .= \sprintf(' since version %s', $filter->getDeprecatedVersion());
            }
            if ($filter->getAlternative()) {
                $message .= \sprintf('. Use "%s" instead', $filter->getAlternative());
            }
            $src = $this->parser->getStream()->getSourceContext();
            $message .= \sprintf(' in %s at line %d.', $src->getPath() ? $src->getPath() : $src->getName(), $line);
            @\trigger_error($message, \E_USER_DEPRECATED);
        }
        if ($filter instanceof \ToolsetAdvancedExport\Twig\TwigFilter) {
            return $filter->getNodeClass();
        }
        return $filter instanceof \ToolsetAdvancedExport\Twig_Filter_Node ? $filter->getClass() : 'Twig\\Node\\Expression\\FilterExpression';
    }
    // checks that the node only contains "constant" elements
    protected function checkConstantExpression(\ToolsetAdvancedExport\Twig_NodeInterface $node)
    {
        if (!($node instanceof \ToolsetAdvancedExport\Twig\Node\Expression\ConstantExpression || $node instanceof \ToolsetAdvancedExport\Twig\Node\Expression\ArrayExpression || $node instanceof \ToolsetAdvancedExport\Twig\Node\Expression\Unary\NegUnary || $node instanceof \ToolsetAdvancedExport\Twig\Node\Expression\Unary\PosUnary)) {
            return \false;
        }
        foreach ($node as $n) {
            if (!$this->checkConstantExpression($n)) {
                return \false;
            }
        }
        return \true;
    }
}
\class_alias('ToolsetAdvancedExport\\Twig\\ExpressionParser', 'ToolsetAdvancedExport\\Twig_ExpressionParser');
