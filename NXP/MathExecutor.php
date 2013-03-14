<?php
/**
 * Author: Alexander "NeonXP" Kiryukhin
 * Date: 14.03.13
 * Time: 1:01
 */
namespace NXP;

/**
 * Class MathExecutor
 * @package NXP
 */
class MathExecutor {
    const LEFT_ASSOCIATED = 'LEFT_ASSOCIATED';
    const RIGHT_ASSOCIATED = 'RIGHT_ASSOCIATED';
    const NOT_ASSOCIATED = 'NOT_ASSOCIATED';

    const UNARY  = 'UNARY';
    const BINARY = 'BINARY';

    private $operators = [ ];

    /**
     * Base math operators
     */
    public function __construct()
    {
        $this->addOperator('+', 1, function ($op1, $op2) { return $op1 + $op2; });
        $this->addOperator('-', 1, function ($op1, $op2) { return $op1 - $op2; });
        $this->addOperator('*', 2, function ($op1, $op2) { return $op1 * $op2; });
        $this->addOperator('/', 2, function ($op1, $op2) { return $op1 / $op2; });
        $this->addOperator('^', 3, function ($op1, $op2) { return pow($op1, $op2); });
    }

    /**
     * Add custom operator
     * @param string $name
     * @param int $priority
     * @param callable $callback
     * @param string $association
     * @param string $type
     */
    public function addOperator($name, $priority, callable $callback, $association = self::LEFT_ASSOCIATED, $type = self::BINARY)
    {
        $this->operators[$name] = [
            'priority'      => $priority,
            'association'   => $association,
            'type'          => $type,
            'callback'      => $callback
        ];
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
     * @return array
     * @throws \Exception
     */
    protected function convertToReversePolishNotation($expression)
    {
        $stack = new \SplStack();
        $queue = [];
        $currentNumber = '';

        for ($i = 0; $i < strlen($expression); $i++)
        {
            $char = substr($expression, $i, 1);
            if  (is_numeric($char) || (($char == '.') && (strpos($currentNumber, '.')===false))) {
                $currentNumber .= $char;
            } elseif ($currentNumber!='') {
                $queue = $this->insertNumberToQueue($currentNumber, $queue);
                $currentNumber = '';
            }
            if (array_key_exists($char, $this->operators)) {
                while ($this->o1HasLowerPriority($char, $stack)) {
                    $queue[] = $stack->pop();
                }
                $stack->push($char);
            }
            if ($char == '(') {
                $stack->push($char);
            }
            if ($char == ')') {
                if ($currentNumber!='') {
                    $queue = $this->insertNumberToQueue($currentNumber, $queue);
                    $currentNumber = '';
                }
                while (($stackChar = $stack->pop()) != '(') {
                    $queue[] = $stackChar;
                }
                /**
                 * @TODO parse functions here
                 */
            }
        }

        if ($currentNumber!='') {
            $queue = $this->insertNumberToQueue($currentNumber, $queue);
        }

        while (!$stack->isEmpty()) {
            $queue[] = ($char = $stack->pop());
            if (!array_key_exists($char, $this->operators)) {
                throw new \Exception('Opening bracket has no closing bracket');
            }
        }

        return $queue;
    }

    /**
     * Calculate value of expression
     * @param array $expression
     * @return int|float
     * @throws \Exception
     */
    protected function calculateReversePolishNotation(array $expression)
    {
        $stack = new \SplStack();
        foreach ($expression as $element) {
            if (is_numeric($element)) {
                $stack->push($element);
            } elseif (array_key_exists($element, $this->operators))  {
                $operator = $this->operators[$element];
                switch ($operator['type']) {
                    case self::BINARY:
                        $op2 = $stack->pop();
                        $op1 = $stack->pop();
                        $operatorResult = $operator['callback']($op1, $op2);
                        break;
                    case self::UNARY:
                        $op = $stack->pop();
                        $operatorResult = $operator['callback']($op);
                        break;
                    default:
                        throw new \Exception('Incorrect type');
                }
                $stack->push($operatorResult);
            }
        }
        $result = $stack->pop();
        if (!$stack->isEmpty()) {
            throw new \Exception('Incorrect expression');
        }

        return $result;
    }

    /**
     * @param $char
     * @param $stack
     * @return bool
     */
    private function o1HasLowerPriority($char, \SplStack $stack) {
        if (($stack->isEmpty()) || ($stack->top() == '(')) {
            return false;
        }
        $stackTopAssociation = $this->operators[$stack->top()]['association'];
        $stackTopPriority = $this->operators[$stack->top()]['priority'];
        $charPriority = $this->operators[$char]['priority'];


        return
            (($stackTopAssociation != self::LEFT_ASSOCIATED) && ($stackTopPriority > $charPriority)) ||
            (($stackTopAssociation == self::LEFT_ASSOCIATED) && ($stackTopPriority >= $charPriority));
    }

    /**
     * @param string $currentNumber
     * @param array $queue
     * @return array
     */
    private function insertNumberToQueue($currentNumber, $queue)
    {
        if ($currentNumber[0]=='.') {
            $currentNumber = '0'.$currentNumber;
        }
        $currentNumber = trim($currentNumber, '.');
        $queue[] = $currentNumber;

        return $queue;
    }

}