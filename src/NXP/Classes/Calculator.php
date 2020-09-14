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
     * @var CustomFunction[]
     */
    private $functions;

    /**
     * @var Operator[]
     */
    private $operators;

    /**
     * Calculator constructor.
     * @param CustomFunction[] $functions
     * @param Operator[] $operators
     */
    public function __construct(array $functions, array $operators)
    {
        $this->functions = $functions;
        $this->operators = $operators;
    }

    /**
     * Calculate array of tokens in reverse polish notation
     * @param Token[] $tokens
     * @param array $variables
     * @return mixed
     * @throws IncorrectExpressionException
     * @throws UnknownVariableException
     */
    public function calculate(array $tokens, array $variables, callable $onVarNotFound = null)
    {
        /** @var Token[] $stack */
        $stack = [];
        foreach ($tokens as $token) {
            if ($token->type === Token::Literal || $token->type === Token::String) {
                $stack[] = $token;
            } elseif ($token->type === Token::Variable) {
                $variable = $token->value;

                $value = null;
                if (array_key_exists($variable, $variables)) {
                    $value = $variables[$variable];
                } elseif ($onVarNotFound) {
                    $value = call_user_func($onVarNotFound, $variable);
                }

                if (!isset($value)) {
                    throw new UnknownVariableException($variable);
                }

                $stack[] = new Token(Token::Literal, $value);
            } elseif ($token->type === Token::Function) {
                if (!array_key_exists($token->value, $this->functions)) {
                    throw new UnknownFunctionException($token->value);
                }
                $stack[] = $this->functions[$token->value]->execute($stack);
            } elseif ($token->type === Token::Operator) {
                if (!array_key_exists($token->value, $this->operators)) {
                    throw new UnknownOperatorException($token->value);
                }
                $stack[] = $this->operators[$token->value]->execute($stack);
            }
        }
        $result = array_pop($stack);
        if ($result === null || !empty($stack)) {
            throw new IncorrectExpressionException('Stack must be empty');
        }
        return $result->value;
    }
}
