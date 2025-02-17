<?php

/**
 * This file is part of the MathExecutor package
 *
 * (c) Alexander Kiryukhin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace NXP\Classes;

use NXP\Exception\IncorrectBracketsException;
use NXP\Exception\UnknownOperatorException;
use RuntimeException;
use SplStack;

/**
 * @author Alexander Kiryukhin <a.kiryukhin@mail.ru>
 */
class Tokenizer
{
    /** @var array<Token> */
    public array $tokens = [];

    private string $numberBuffer = '';

    private string $stringBuffer = '';

    private bool $allowNegative = true;

    private bool $inSingleQuotedString = false;

    private bool $inDoubleQuotedString = false;

    /**
     * Tokenizer constructor.
     * @param Operator[] $operators
     */
    public function __construct(private string $input, private array $operators)
    {
    }

    public function tokenize() : self
    {
        $isLastCharEscape = false;

        foreach (\str_split($this->input) as $ch) {
            switch (true) {
                case $this->inSingleQuotedString:
                    if ('\\' === $ch) {
                        if ($isLastCharEscape) {
                            $this->stringBuffer .= '\\';
                            $isLastCharEscape = false;
                        } else {
                            $isLastCharEscape = true;
                        }

                        continue 2;
                    } elseif ("'" === $ch) {
                        if ($isLastCharEscape) {
                            $this->stringBuffer .= "'";
                            $isLastCharEscape = false;
                        } else {
                            $this->tokens[] = new Token(Token::String, $this->stringBuffer);
                            $this->inSingleQuotedString = false;
                            $this->stringBuffer = '';
                        }

                        continue 2;
                    }

                    if ($isLastCharEscape) {
                        $this->stringBuffer .= '\\';
                        $isLastCharEscape = false;
                    }
                    $this->stringBuffer .= $ch;

                    continue 2;

                case $this->inDoubleQuotedString:
                    if ('\\' === $ch) {
                        if ($isLastCharEscape) {
                            $this->stringBuffer .= '\\';
                            $isLastCharEscape = false;
                        } else {
                            $isLastCharEscape = true;
                        }

                        continue 2;
                    } elseif ('"' === $ch) {
                        if ($isLastCharEscape) {
                            $this->stringBuffer .= '"';
                            $isLastCharEscape = false;
                        } else {
                            $this->tokens[] = new Token(Token::String, $this->stringBuffer);
                            $this->inDoubleQuotedString = false;
                            $this->stringBuffer = '';
                        }

                        continue 2;
                    }

                    if ($isLastCharEscape) {
                        $this->stringBuffer .= '\\';
                        $isLastCharEscape = false;
                    }
                    $this->stringBuffer .= $ch;

                    continue 2;

                case '[' === $ch:
                    $this->tokens[] = new Token(Token::Function, 'array');
                    $this->allowNegative = true;
                    $this->tokens[] = new Token(Token::LeftParenthesis, '');

                    continue 2;

                case ' ' == $ch || "\n" == $ch || "\r" == $ch || "\t" == $ch:
                    $this->emptyNumberBufferAsLiteral();
                    $this->emptyStrBufferAsVariable();
                    $this->tokens[] = new Token(Token::Space, '');

                    continue 2;

                case $this->isNumber($ch):
                    if ('' != $this->stringBuffer) {
                        $this->stringBuffer .= $ch;

                        continue 2;
                    }
                    $this->numberBuffer .= $ch;
                    $this->allowNegative = false;

                    break;

                /** @noinspection PhpMissingBreakStatementInspection */
                case 'e' === \strtolower($ch):
                    if (\strlen($this->numberBuffer) && \str_contains($this->numberBuffer, '.')) {
                        $this->numberBuffer .= 'e';
                        $this->allowNegative = false;

                        break;
                    }

                // no break
                // Intentionally fall through
                case $this->isAlpha($ch):
                    if (\strlen($this->numberBuffer)) {
                        $this->emptyNumberBufferAsLiteral();
                        $this->tokens[] = new Token(Token::Operator, '*');
                    }
                    $this->allowNegative = false;
                    $this->stringBuffer .= $ch;

                    break;

                case '"' == $ch:
                    $this->inDoubleQuotedString = true;

                    continue 2;

                case "'" == $ch:
                    $this->inSingleQuotedString = true;

                    continue 2;

                case $this->isDot($ch):
                    $this->numberBuffer .= $ch;
                    $this->allowNegative = false;

                    break;

                case $this->isLP($ch):
                    if ('' != $this->stringBuffer) {
                        $this->tokens[] = new Token(Token::Function, $this->stringBuffer);
                        $this->stringBuffer = '';
                    } elseif (\strlen($this->numberBuffer)) {
                        $this->emptyNumberBufferAsLiteral();
                        $this->tokens[] = new Token(Token::Operator, '*');
                    }
                    $this->allowNegative = true;
                    $this->tokens[] = new Token(Token::LeftParenthesis, '');

                    break;

                case $this->isRP($ch) || ']' === $ch :
                    $this->emptyNumberBufferAsLiteral();
                    $this->emptyStrBufferAsVariable();
                    $this->allowNegative = false;
                    $this->tokens[] = new Token(Token::RightParenthesis, '');

                    break;

                case $this->isComma($ch):
                    $this->emptyNumberBufferAsLiteral();
                    $this->emptyStrBufferAsVariable();
                    $this->allowNegative = true;
                    $this->tokens[] = new Token(Token::ParamSeparator, '');

                    break;

                default:
                    // special case for unary operations
                    if ('-' == $ch || '+' == $ch) {
                        if ($this->allowNegative) {
                            $this->allowNegative = false;
                            $this->tokens[] = new Token(Token::Operator, '-' == $ch ? 'uNeg' : 'uPos');

                            continue 2;
                        }

                        // could be in exponent, in which case negative should be added to the numberBuffer
                        if ($this->numberBuffer && 'e' == $this->numberBuffer[\strlen($this->numberBuffer) - 1]) {
                            $this->numberBuffer .= $ch;

                            continue 2;
                        }
                    }
                    $this->emptyNumberBufferAsLiteral();
                    $this->emptyStrBufferAsVariable();

                    if ('$' != $ch) {
                        if (\count($this->tokens) > 0) {
                            if (Token::Operator === $this->tokens[\count($this->tokens) - 1]->type) {
                                $this->tokens[\count($this->tokens) - 1]->value .= $ch;
                            } else {
                                $this->tokens[] = new Token(Token::Operator, $ch);
                            }
                        } else {
                            $this->tokens[] = new Token(Token::Operator, $ch);
                        }
                    }
                    $this->allowNegative = true;
            }
        }
        $this->emptyNumberBufferAsLiteral();
        $this->emptyStrBufferAsVariable();

        return $this;
    }

    /**
     * @throws IncorrectBracketsException
     * @throws UnknownOperatorException
     * @return Token[] Array of tokens in revers polish notation
     */
    public function buildReversePolishNotation() : array
    {
        $tokens = [];
        /** @var SplStack<Token> $stack */
        $stack = new SplStack();
        /**
         * @var SplStack<int> $paramCounter
         */
        $paramCounter = new SplStack();

        foreach ($this->tokens as $token) {
            switch ($token->type) {
                case Token::Literal:
                case Token::Variable:
                case Token::String:
                    $tokens[] = $token;

                    if ($paramCounter->count() > 0 && 0 === $paramCounter->top()) {
                        $paramCounter->push($paramCounter->pop() + 1);
                    }

                    break;

                case Token::Function:
                    if ($paramCounter->count() > 0 && 0 === $paramCounter->top()) {
                        $paramCounter->push($paramCounter->pop() + 1);
                    }
                    $stack->push($token);
                    $paramCounter->push(0);

                    break;

                case Token::LeftParenthesis:
                    $stack->push($token);

                    break;

                case Token::ParamSeparator:
                    while (Token::LeftParenthesis !== $stack->top()->type) {
                        if (0 === $stack->count()) {
                            throw new IncorrectBracketsException();
                        }
                        $tokens[] = $stack->pop();
                    }
                    $paramCounter->push($paramCounter->pop() + 1);

                    break;

                case Token::Operator:
                    if (! \array_key_exists($token->value, $this->operators)) {
                        throw new UnknownOperatorException($token->value);
                    }
                    $op1 = $this->operators[$token->value];

                    while ($stack->count() > 0 && Token::Operator === $stack->top()->type) {
                        if (! \array_key_exists($stack->top()->value, $this->operators)) {
                            throw new UnknownOperatorException($stack->top()->value);
                        }
                        $op2 = $this->operators[$stack->top()->value];

                        if ($op2->priority >= $op1->priority) {
                            $tokens[] = $stack->pop();

                            continue;
                        }

                        break;
                    }
                    $stack->push($token);

                    break;

                case Token::RightParenthesis:
                    while (true) {
                        try {
                            $ctoken = $stack->pop();

                            if (Token::LeftParenthesis === $ctoken->type) {
                                break;
                            }
                            $tokens[] = $ctoken;
                        } catch (RuntimeException) {
                            throw new IncorrectBracketsException();
                        }
                    }

                    if ($stack->count() > 0 && Token::Function == $stack->top()->type) {
                        /**
                         * @var Token $f
                         */
                        $f = $stack->pop();
                        $f->paramCount = $paramCounter->pop();
                        $tokens[] = $f;
                    }

                    break;

                case Token::Space:
                    //do nothing
            }
        }

        while (0 !== $stack->count()) {
            if (Token::LeftParenthesis === $stack->top()->type || Token::RightParenthesis === $stack->top()->type) {
                throw new IncorrectBracketsException();
            }

            if (Token::Space === $stack->top()->type) {
                $stack->pop();

                continue;
            }
            $tokens[] = $stack->pop();
        }

        return $tokens;
    }

    private function isNumber(string $ch) : bool
    {
        return $ch >= '0' && $ch <= '9';
    }

    private function isAlpha(string $ch) : bool
    {
        return $ch >= 'a' && $ch <= 'z' || $ch >= 'A' && $ch <= 'Z' || '_' == $ch;
    }

    private function emptyNumberBufferAsLiteral() : void
    {
        if (\strlen($this->numberBuffer)) {
            $this->tokens[] = new Token(Token::Literal, $this->numberBuffer);
            $this->numberBuffer = '';
        }
    }

    private function isDot(string $ch) : bool
    {
        return '.' == $ch;
    }

    private function isLP(string $ch) : bool
    {
        return '(' == $ch;
    }

    private function isRP(string $ch) : bool
    {
        return ')' == $ch;
    }

    private function emptyStrBufferAsVariable() : void
    {
        if ('' != $this->stringBuffer) {
            $this->tokens[] = new Token(Token::Variable, $this->stringBuffer);
            $this->stringBuffer = '';
        }
    }

    private function isComma(string $ch) : bool
    {
        return ',' == $ch;
    }
}
