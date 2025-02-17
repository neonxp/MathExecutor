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

use NXP\Exception\IncorrectExpressionException;
use NXP\Exception\UnknownFunctionException;
use NXP\Exception\UnknownOperatorException;
use NXP\Exception\UnknownVariableException;

/**
 * @author Alexander Kiryukhin <a.kiryukhin@mail.ru>
 */
class Calculator
{
    /**
     * @todo PHP8: Use constructor property promotion -> public function __construct(private array $functions, private array $operators)
     *
     * @param array<string, CustomFunction> $functions
     * @param array<Operator> $operators
     */
    public function __construct(private array $functions, private array $operators)
    {
    }

    /**
     * Calculate array of tokens in reverse polish notation
     *
     * @param Token[]                     $tokens
     * @param array<string, float|string> $variables
     *
     * @throws UnknownVariableException
     * @throws IncorrectExpressionException
     * @return int|float|string|null
     */
    public function calculate(array $tokens, array $variables, ?callable $onVarNotFound = null)
    {
        /** @var Token[] $stack */
        $stack = [];

        foreach ($tokens as $token) {
            if (Token::Literal === $token->type || Token::String === $token->type) {
                $stack[] = $token;
            } elseif (Token::Variable === $token->type) {
                $variable = $token->value;

                $value = null;

                if (\array_key_exists($variable, $variables)) {
                    $value = $variables[$variable];
                } elseif ($onVarNotFound) {
                    $value = \call_user_func($onVarNotFound, $variable);
                } else {
                    throw new UnknownVariableException($variable);
                }

                $stack[] = new Token(Token::Literal, $value, $variable);
            } elseif (Token::Function === $token->type) {
                if (! \array_key_exists($token->value, $this->functions)) {
                    throw new UnknownFunctionException($token->value);
                }
                $stack[] = $this->functions[$token->value]->execute($stack, $token->paramCount);
            } elseif (Token::Operator === $token->type) {
                if (! \array_key_exists($token->value, $this->operators)) {
                    throw new UnknownOperatorException($token->value);
                }
                $stack[] = $this->operators[$token->value]->execute($stack);
            }
        }
        $result = \array_pop($stack);

        if (null === $result || ! empty($stack)) {
            throw new IncorrectExpressionException('Stack must be empty');
        }

        return $result->value;
    }
}
