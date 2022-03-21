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
    /**
     * @var Token[]
     */
    public $tokens = [];
    /**
     * @var string
     */
    private $input = '';
    /**
     * @var string
     */
    private $numberBuffer = '';
    /**
     * @var string
     */
    private $stringBuffer = '';
    /**
     * @var bool
     */
    private $allowNegative = true;
    /**
     * @var Operator[]
     */
    private $operators = [];

    /**
     * @var bool
     */
    private $inSingleQuotedString = false;

    /**
     * @var bool
     */
    private $inDoubleQuotedString = false;

    /**
     * Tokenizer constructor.
     * @param string $input
     * @param Operator[] $operators
     */
    public function __construct(string $input, array $operators)
    {
        $this->input = $input;
        $this->operators = $operators;
    }

    public function tokenize(): self
    {
        foreach (str_split($this->input, 1) as $ch) {
            switch (true) {
                case $this->inSingleQuotedString:
                    if ($ch === "'") {
                        $this->tokens[] = new Token(Token::String, $this->stringBuffer);
                        $this->inSingleQuotedString = false;
                        $this->stringBuffer = '';
                        continue 2;
                    }
                    $this->stringBuffer .= $ch;
                    continue 2;
                case $this->inDoubleQuotedString:
                    if ($ch === '"') {
                        $this->tokens[] = new Token(Token::String, $this->stringBuffer);
                        $this->inDoubleQuotedString = false;
                        $this->stringBuffer = '';
                        continue 2;
                    }
                    $this->stringBuffer .= $ch;
                    continue 2;
                case $ch == ' ' || $ch == "\n" || $ch == "\r" || $ch == "\t":
                    $this->tokens[] = new Token(Token::Space, '');
                    continue 2;
                case $this->isNumber($ch):
                    if ($this->stringBuffer != '') {
                        $this->stringBuffer .= $ch;
                        continue 2;
                    }
                    $this->numberBuffer .= $ch;
                    $this->allowNegative = false;
                    break;
                /** @noinspection PhpMissingBreakStatementInspection */
                case strtolower($ch) === 'e':
                    if (strlen($this->numberBuffer) && strpos($this->numberBuffer, '.') !== false) {
                        $this->numberBuffer .= 'e';
                        $this->allowNegative = false;
                        break;
                    }
                    // no break
                case $this->isAlpha($ch):
                    if (strlen($this->numberBuffer)) {
                        $this->emptyNumberBufferAsLiteral();
                        $this->tokens[] = new Token(Token::Operator, '*');
                    }
                    $this->allowNegative = false;
                    $this->stringBuffer .= $ch;
                    break;
                case $ch == '"':
                    $this->inDoubleQuotedString = true;
                    continue 2;
                case $ch == "'":
                    $this->inSingleQuotedString = true;
                    continue 2;

                case $this->isDot($ch):
                    $this->numberBuffer .= $ch;
                    $this->allowNegative = false;
                    break;
                case $this->isLP($ch):
                    if ($this->stringBuffer != '') {
                        $this->tokens[] = new Token(Token::Function, $this->stringBuffer);
                        $this->stringBuffer = '';
                    } elseif (strlen($this->numberBuffer)) {
                        $this->emptyNumberBufferAsLiteral();
                        $this->tokens[] = new Token(Token::Operator, '*');
                    }
                    $this->allowNegative = true;
                    $this->tokens[] = new Token(Token::LeftParenthesis, '');
                    break;
                case $this->isRP($ch):
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
                    if ($ch == '-' || $ch == '+') {
                        if ($this->allowNegative) {
                            $this->allowNegative = false;
                            $this->tokens[] = new Token(Token::Operator, $ch == '-' ? 'uNeg' : 'uPos');
                            continue 2;
                        }
                        // could be in exponent, in which case negative should be added to the numberBuffer
                        if ($this->numberBuffer && $this->numberBuffer[strlen($this->numberBuffer) - 1] == 'e') {
                            $this->numberBuffer .= $ch;
                            continue 2;
                        }
                    }
                    $this->emptyNumberBufferAsLiteral();
                    $this->emptyStrBufferAsVariable();
                    if ($ch != '$') {
                        if (count($this->tokens) > 0) {
                            if ($this->tokens[count($this->tokens) - 1]->type === Token::Operator) {
                                $this->tokens[count($this->tokens) - 1]->value .= $ch;
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

    private function isNumber(string $ch): bool
    {
        return $ch >= '0' && $ch <= '9';
    }

    private function isAlpha(string $ch): bool
    {
        return $ch >= 'a' && $ch <= 'z' || $ch >= 'A' && $ch <= 'Z' || $ch == '_';
    }

    private function emptyNumberBufferAsLiteral(): void
    {
        if (strlen($this->numberBuffer)) {
            $this->tokens[] = new Token(Token::Literal, $this->numberBuffer);
            $this->numberBuffer = '';
        }
    }

    private function isDot(string $ch): bool
    {
        return $ch == '.';
    }

    private function isLP(string $ch): bool
    {
        return $ch == '(';
    }

    private function isRP(string $ch): bool
    {
        return $ch == ')';
    }

    private function emptyStrBufferAsVariable(): void
    {
        if ($this->stringBuffer != '') {
            $this->tokens[] = new Token(Token::Variable, $this->stringBuffer);
            $this->stringBuffer = '';
        }
    }

    private function isComma(string $ch): bool
    {
        return $ch == ',';
    }

    /**
     * @return Token[] Array of tokens in revers polish notation
     * @throws IncorrectBracketsException
     * @throws UnknownOperatorException
     */
    public function buildReversePolishNotation(): array
    {
        $tokens = [];
        /** @var SplStack<Token> $stack */
        $stack = new SplStack();
        foreach ($this->tokens as $token) {
            switch ($token->type) {
                case Token::Literal:
                case Token::Variable:
                case Token::String:
                    $tokens[] = $token;
                    break;
                case Token::Function:
                case Token::LeftParenthesis:
                    $stack->push($token);
                    break;
                case Token::ParamSeparator:
                    while ($stack->top()->type !== Token::LeftParenthesis) {
                        if ($stack->count() === 0) {
                            throw new IncorrectBracketsException();
                        }
                        $tokens[] = $stack->pop();
                    }
                    break;
                case Token::Operator:
                    if (!array_key_exists($token->value, $this->operators)) {
                        throw new UnknownOperatorException($token->value);
                    }
                    $op1 = $this->operators[$token->value];
                    while ($stack->count() > 0 && $stack->top()->type === Token::Operator) {
                        if (!array_key_exists($stack->top()->value, $this->operators)) {
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
                            if ($ctoken->type === Token::LeftParenthesis) {
                                break;
                            }
                            $tokens[] = $ctoken;
                        } catch (RuntimeException $e) {
                            throw new IncorrectBracketsException();
                        }
                    }
                    if ($stack->count() > 0 && $stack->top()->type == Token::Function) {
                        $tokens[] = $stack->pop();
                    }
                    break;
                case Token::Space:
                    //do nothing
            }
        }
        while ($stack->count() !== 0) {
            if ($stack->top()->type === Token::LeftParenthesis || $stack->top()->type === Token::RightParenthesis) {
                throw new IncorrectBracketsException();
            }
            if ($stack->top()->type === Token::Space) {
                $stack->pop();
                continue;
            }
            $tokens[] = $stack->pop();
        }
        return $tokens;
    }
}
