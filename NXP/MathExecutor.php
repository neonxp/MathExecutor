<?php
/**
 * Author: Alexander "NeonXP" Kiryukhin
 * Date: 14.03.13
 * Time: 1:01
 */
namespace NXP;

use NXP\Classes\Func;
use NXP\Classes\Operand;
use NXP\Classes\Token;
use NXP\Classes\TokenParser;

/**
 * Class MathExecutor
 * @package NXP
 */
class MathExecutor {


    private $operators = [ ];

    private $functions = [ ];

    private $variables = [ ];

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

    public function addOperator(Operand $operator)
    {
        $this->operators[$operator->getSymbol()] = $operator;
    }

    public function addFunction(Func $function)
    {
        $this->functions[$function->getName()] = $function->getCallback();
    }

    public function setVar($variable, $value)
    {
        if (!is_numeric($value)) {
            throw new \Exception("Variable value must be a number");
        }
        $this->variables[$variable] = $value;
    }

    /**
     * Execute expression
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
     * @param $expression
     * @return \SplQueue
     * @throws \Exception
     */
    protected function convertToReversePolishNotation($expression)
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
                    $string = $this->stack->pop()->getValue();
                    if (!array_key_exists($string, $this->functions)) {
                        throw new \Exception('Unknown function');
                    }
                    $this->queue->push(new Token(Token::FUNC, $string));
                }
                break;

            case Token::OPERATOR:
                if (!array_key_exists($token->getValue(), $this->operators)) {
                    throw new \Exception("Unknown operator '{$token->getValue()}'");
                }

                $this->proceedOperator($token);
                $this->stack->push($token);
                break;

            default:
                throw new \Exception('Unknown token');
        }
    }

    private function proceedOperator($token)
    {
        if (!array_key_exists($token->getValue(), $this->operators)) {
            throw new \Exception('Unknown operator');
        }
        /** @var Operand $operator */
        $operator = $this->operators[$token->getValue()];
        while (!$this->stack->isEmpty()) {
            $top = $this->stack->top();
            if ($top->getType() == Token::OPERATOR) {
                $priority = $this->operators[$top->getValue()]->getPriority();
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

    protected function calculateReversePolishNotation(\SplQueue $expression)
    {
        $this->stack = new \SplStack();
        /** @val Token $token */
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


                    $this->stack->push(new Token(Token::NUMBER, ($callback($arg1, $arg2))));
                    break;
                case Token::FUNC:
                    /** @var Func $function */
                    $callback = $this->functions[$token->getValue()];
                    $arg = $this->stack->pop()->getValue();
                    $this->stack->push(new Token(Token::NUMBER, ($callback($arg))));
                    break;
                default:
                    throw new \Exception('Unknown token');
            }
        }
        $result = $this->stack->pop()->getValue();
        if (!$this->stack->isEmpty()) {
            throw new \Exception('Incorrect expression');
        }

        return $result;
    }
}