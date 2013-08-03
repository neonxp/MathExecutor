<?php

/**
 * This file is part of the MathExecutor package
 *
 * (c) Alexander Kiryukhin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace NXP;

use NXP\Classes\Func;
use NXP\Classes\Operand;
use NXP\Classes\Token;
use NXP\Classes\TokenParser;
use NXP\Exception\IncorrectExpressionException;
use NXP\Exception\UnknownFunctionException;
use NXP\Exception\UnknownOperatorException;
use NXP\Exception\UnknownTokenException;

/**
 * Class MathExecutor
 * @package NXP
 */
class MathExecutor
{
    /**
     * Available operators
     *
     * @var array
     */
    private $operators = array();

    /**
     * Available functions
     *
     * @var array
     */
    private $functions = array();

    /**
     * Available variables
     *
     * @var array
     */
    private $variables = array();

    /**
     * @var \SplStack
     */
    private $stack;

    /**
     * @var \SplQueue
     */
    private $queue;

    /**
     * Base math operators
     */
    public function __construct()
    {
        $this->addDefaults();
    }

    public function __clone()
    {
        $this->variables = array();
        $this->operators = array();
        $this->functions = array();

        $this->addDefaults();
    }

    /**
     * Set default operands and functions
     */
    protected function addDefaults()
    {
        $this->addOperator(new Operand('+', 1, Operand::LEFT_ASSOCIATED, Operand::BINARY, function ($op1, $op2) { return $op1+$op2; }));
        $this->addOperator(new Operand('-', 1, Operand::LEFT_ASSOCIATED, Operand::BINARY, function ($op1, $op2) { return $op1-$op2; }));
        $this->addOperator(new Operand('*', 2, Operand::LEFT_ASSOCIATED, Operand::BINARY, function ($op1, $op2) { return $op1*$op2; }));
        $this->addOperator(new Operand('/', 2, Operand::LEFT_ASSOCIATED, Operand::BINARY, function ($op1, $op2) { return $op1/$op2; }));
        $this->addOperator(new Operand('^', 3, Operand::LEFT_ASSOCIATED, Operand::BINARY, function ($op1, $op2) { return pow($op1,$op2); }));

        $this->addFunction(new Func('sin',  function ($arg) { return sin($arg); }));
        $this->addFunction(new Func('cos',  function ($arg) { return cos($arg); }));
        $this->addFunction(new Func('tn',   function ($arg) { return tan($arg); }));
        $this->addFunction(new Func('asin', function ($arg) { return asin($arg); }));
        $this->addFunction(new Func('acos', function ($arg) { return acos($arg); }));
        $this->addFunction(new Func('atn',  function ($arg) { return atan($arg); }));
    }

    /**
     * Add operator to executor
     *
     * @param  Operand      $operator
     * @return MathExecutor
     */
    public function addOperator(Operand $operator)
    {
        $this->operators[$operator->getSymbol()] = $operator;

        return $this;
    }

    /**
     * Add function to executor
     *
     * @param  string       $name
     * @param  callable     $function
     * @return MathExecutor
     */
    public function addFunction($name, callable $function = null)
    {
        if ($name instanceof Func) {
            $this->functions[$name->getName()] = $name->getCallback();
        } else {
            $this->functions[$name] = $function;
        }

        return $this;
    }

    /**
     * Add variable to executor
     *
     * @param  string        $variable
     * @param  integer|float $value
     * @throws \Exception
     * @return MathExecutor
     */
    public function setVar($variable, $value)
    {
        if (!is_numeric($value)) {
            throw new \Exception("Variable value must be a number");
        }

        $this->variables[$variable] = $value;

        return $this;
    }

    /**
     * Add variables to executor
     *
     * @param  array        $variables
     * @param  bool         $clear     Clear previous variables
     * @return MathExecutor
     */
    public function setVars(array $variables, $clear = true)
    {
        if ($clear) {
            $this->removeVars();
        }

        foreach ($variables as $name => $value) {
            $this->setVar($name, $value);
        }

        return $this;
    }

    /**
     * Remove variable from executor
     *
     * @param  string       $variable
     * @return MathExecutor
     */
    public function removeVar($variable)
    {
        unset ($this->variables[$variable]);

        return $this;
    }

    /**
     * Remove all variables
     */
    public function removeVars()
    {
        $this->variables = array();

        return $this;
    }

    /**
     * Execute expression
     *
     * @param $expression
     * @return int|float
     */
    public function execute($expression)
    {
        $reversePolishNotation = $this->convertToReversePolishNotation($expression);
        $result = $this->calculateReversePolishNotation($reversePolishNotation);

        return $result;
    }

    /**
     * Convert expression from normal expression form to RPN
     *
     * @param $expression
     * @return \SplQueue
     * @throws \Exception
     */
    private function convertToReversePolishNotation($expression)
    {
        $this->stack = new \SplStack();
        $this->queue = new \SplQueue();

        $tokenParser = new TokenParser();
        $input = $tokenParser->tokenize($expression);

        foreach ($input as $token) {
            $this->categorizeToken($token);
        }

        while (!$this->stack->isEmpty()) {
            $token = $this->stack->pop();

            if ($token->getType() != Token::OPERATOR) {
                throw new \Exception('Opening bracket without closing bracket');
            }

            $this->queue->push($token);
        }

        return $this->queue;
    }

    /**
     * @param  Token      $token
     * @throws \Exception
     */
    private function categorizeToken(Token $token)
    {
        switch ($token->getType()) {
            case Token::NUMBER :
                $this->queue->push($token);
                break;

            case Token::STRING:
                if (array_key_exists($token->getValue(), $this->variables)) {
                    $this->queue->push(new Token(Token::NUMBER, $this->variables[$token->getValue()]));
                } else {
                    $this->stack->push($token);
                }
                break;

            case Token::LEFT_BRACKET:
                $this->stack->push($token);
                break;

            case Token::RIGHT_BRACKET:
                $previousToken = $this->stack->pop();
                while (!$this->stack->isEmpty() && ($previousToken->getType() != Token::LEFT_BRACKET)) {
                    $this->queue->push($previousToken);
                    $previousToken = $this->stack->pop();
                }
                if ((!$this->stack->isEmpty()) && ($this->stack->top()->getType() == Token::STRING)) {
                    $funcName = $this->stack->pop()->getValue();
                    if (!array_key_exists($funcName, $this->functions)) {
                        throw new UnknownFunctionException(sprintf(
                            'Unknown function: "%s".',
                            $funcName
                        ));
                    }
                    $this->queue->push(new Token(Token::FUNC, $funcName));
                }
                break;

            case Token::OPERATOR:
                if (!array_key_exists($token->getValue(), $this->operators)) {
                    throw new UnknownOperatorException(sprintf(
                        'Unknown operator: "%s".',
                        $token->getValue()
                    ));
                }

                $this->proceedOperator($token);
                $this->stack->push($token);
                break;

            default:
                throw new UnknownTokenException(sprintf(
                    'Unknown token: "%s".',
                    $token->getValue()
                ));
        }
    }

    /**
     * @param $token
     * @throws \Exception
     */
    private function proceedOperator(Token $token)
    {
        if (!array_key_exists($token->getValue(), $this->operators)) {
            throw new UnknownOperatorException(sprintf(
                'Unknown operator: "%s".',
                $token->getValue()
            ));
        }

        /** @var Operand $operator */
        $operator = $this->operators[$token->getValue()];

        while (!$this->stack->isEmpty()) {
            $top = $this->stack->top();

            if ($top->getType() == Token::OPERATOR) {
                /** @var Operand $operator */
                $operator = $this->operators[$top->getValue()];
                $priority = $operator->getPriority();
                if ( $operator->getAssociation() == Operand::RIGHT_ASSOCIATED) {
                    if (($priority > $operator->getPriority())) {
                        $this->queue->push($this->stack->pop());
                    } else {
                        return;
                    }
                } else {
                    if (($priority >= $operator->getPriority())) {
                        $this->queue->push($this->stack->pop());
                    } else {
                        return;
                    }
                }
            } elseif ($top->getType() == Token::STRING) {
                $this->queue->push($this->stack->pop());
            } else {
                return;
            }
        }
    }

    /**
     * @param  \SplQueue  $expression
     * @return mixed
     * @throws \Exception
     */
    private function calculateReversePolishNotation(\SplQueue $expression)
    {
        $this->stack = new \SplStack();
        /** @var Token $token */
        foreach ($expression as $token) {
            switch ($token->getType()) {
                case Token::NUMBER :
                    $this->stack->push($token);
                    break;

                case Token::OPERATOR:
                    /** @var Operand $operator */
                    $operator = $this->operators[$token->getValue()];
                    if ($operator->getType() == Operand::BINARY) {
                        $arg2 = $this->stack->pop()->getValue();
                        $arg1 = $this->stack->pop()->getValue();
                    } else {
                        $arg2 = null;
                        $arg1 = $this->stack->pop()->getValue();
                    }
                    $callback = $operator->getCallback();

                    $this->stack->push(new Token(Token::NUMBER, (call_user_func($callback, $arg1, $arg2))));
                    break;

                case Token::FUNC:
                    /** @var Func $function */
                    $callback = $this->functions[$token->getValue()];
                    $arg = $this->stack->pop()->getValue();
                    $this->stack->push(new Token(Token::NUMBER, (call_user_func($callback, $arg))));
                    break;

                default:
                    throw new UnknownTokenException(sprintf(
                        'Unknown token: "%s".',
                        $token->getValue()
                    ));
            }
        }

        $result = $this->stack->pop()->getValue();

        if (!$this->stack->isEmpty()) {
            throw new IncorrectExpressionException('Incorrect expression.');
        }

        return $result;
    }
}
