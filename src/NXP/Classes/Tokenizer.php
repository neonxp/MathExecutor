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

use NXP\Classes\Token\AbstractOperator;
use NXP\Classes\Token\InterfaceOperator;
use NXP\Classes\Token\InterfaceToken;
use NXP\Classes\Token\TokenComma;
use NXP\Classes\Token\TokenFunction;
use NXP\Classes\Token\TokenLeftBracket;
use NXP\Classes\Token\TokenMinus;
use NXP\Classes\Token\TokenNumber;
use NXP\Classes\Token\TokenRightBracket;
use NXP\Classes\Token\TokenStringDoubleQuoted;
use NXP\Classes\Token\TokenStringSingleQuoted;
use NXP\Classes\Token\TokenVariable;
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
    private $input = "";
    /**
     * @var string
     */
    private $numberBuffer = "";
    /**
     * @var string
     */
    private $stringBuffer = "";
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

    public function tokenize()
    {
        foreach (mb_str_split($this->input, 1) as $ch) {
            switch (true) {
                case $this->inSingleQuotedString:
                    if ($ch === "'") {
                        $this->tokens[] = new Token(Token::String, $this->stringBuffer);
                        $this->inSingleQuotedString = false;
                        $this->stringBuffer = "";
                        continue 2;
                    }
                    $this->stringBuffer .= $ch;
                    continue 2;
                case $this->inDoubleQuotedString:
                    if ($ch === "\"") {
                        $this->tokens[] = new Token(Token::String, $this->stringBuffer);
                        $this->inDoubleQuotedString = false;
                        $this->stringBuffer = "";
                        continue 2;
                    }
                    $this->stringBuffer .= $ch;
                    continue 2;
                case $ch == " " || $ch == "\n" || $ch == "\r" || $ch == "\t":
                    $this->tokens[] = new Token(Token::Space, "");
                    continue 2;
                case $this->isNumber($ch):
                    if ($this->stringBuffer != "") {
                        $this->stringBuffer .= $ch;
                        continue 2;
                    }
                    $this->numberBuffer .= $ch;
                    $this->allowNegative = false;
                    break;
                case strtolower($ch) === "e":
                    if ($this->numberBuffer != "" && strpos($this->numberBuffer, ".") !== false) {
                        $this->numberBuffer .= "e";
                        $this->allowNegative = true;
                        break;
                    }
                case $this->isAlpha($ch):
                    if ($this->numberBuffer != "") {
                        $this->emptyNumberBufferAsLiteral();
                        $this->tokens[] = new Token(Token::Operator, "*");
                    }
                    $this->allowNegative = false;
                    $this->stringBuffer .= $ch;
                    break;
                case $ch == "\"":
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
                    if ($this->stringBuffer != "") {
                        $this->tokens[] = new Token(Token::Function, $this->stringBuffer);
                        $this->stringBuffer = "";
                    } elseif ($this->numberBuffer != "") {
                        $this->emptyNumberBufferAsLiteral();
                        $this->tokens[] = new Token(Token::Operator, "*");
                    }
                    $this->allowNegative = true;
                    $this->tokens[] = new Token(Token::LeftParenthesis, "");
                    break;
                case $this->isRP($ch):
                    $this->emptyNumberBufferAsLiteral();
                    $this->emptyStrBufferAsVariable();
                    $this->allowNegative = false;
                    $this->tokens[] = new Token(Token::RightParenthesis, "");
                    break;
                case $this->isComma($ch):
                    $this->emptyNumberBufferAsLiteral();
                    $this->emptyStrBufferAsVariable();
                    $this->allowNegative = true;
                    $this->tokens[] = new Token(Token::ParamSeparator, "");
                    break;
                default:
                    if ($this->allowNegative && $ch == "-") {
                        $this->allowNegative = false;
                        $this->numberBuffer .= "-";
                        continue 2;
                    }
                    $this->emptyNumberBufferAsLiteral();
                    $this->emptyStrBufferAsVariable();
                    if (count($this->tokens) > 0) {
                        if ($this->tokens[count($this->tokens) - 1]->type === Token::Operator) {
                            $this->tokens[count($this->tokens) - 1]->value .= $ch;
                        } else {
                            $this->tokens[] = new Token(Token::Operator, $ch);
                        }
                    } else {
                        $this->tokens[] = new Token(Token::Operator, $ch);
                    }
                    $this->allowNegative = true;
            }
        }
        $this->emptyNumberBufferAsLiteral();
        $this->emptyStrBufferAsVariable();
        return $this;
    }

    private function isNumber($ch)
    {
        return $ch >= '0' && $ch <= '9';
    }

    private function isAlpha($ch)
    {
        return $ch >= 'a' && $ch <= 'z' || $ch >= 'A' && $ch <= 'Z' || $ch == '_';
    }

    private function emptyNumberBufferAsLiteral()
    {
        if ($this->numberBuffer != "") {
            $this->tokens[] = new Token(Token::Literal, $this->numberBuffer);
            $this->numberBuffer = "";
        }
    }

    private function isDot($ch)
    {
        return $ch == '.';
    }

    private function isLP($ch)
    {
        return $ch == '(';
    }

    private function isRP($ch)
    {
        return $ch == ')';
    }

    private function emptyStrBufferAsVariable()
    {
        if ($this->stringBuffer != "") {
            $this->tokens[] = new Token(Token::Variable, $this->stringBuffer);
            $this->stringBuffer = "";
        }
    }

    private function isComma($ch)
    {
        return $ch == ',';
    }

    /**
     * @return Token[] Array of tokens in revers polish notation
     * @throws IncorrectBracketsException
     * @throws UnknownOperatorException
     */
    public function buildReversePolishNotation()
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
                        throw new UnknownOperatorException();
                    }
                    $op1 = $this->operators[$token->value];
                    while ($stack->count() > 0 && $stack->top()->type === Token::Operator) {
                        if (!array_key_exists($stack->top()->value, $this->operators)) {
                            throw new UnknownOperatorException();
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

