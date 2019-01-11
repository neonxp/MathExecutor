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

use NXP\Classes\Calculator;
use NXP\Classes\Lexer;
use NXP\Classes\TokenFactory;
use NXP\Exception\UnknownVariableException;

/**
 * Class MathExecutor
 * @package NXP
 */
class MathExecutor
{
    /**
     * Available variables
     *
     * @var array
     */
    private $variables = [];

    /**
     * @var TokenFactory
     */
    private $tokenFactory;

    /**
     * @var array
     */
    private $cache = [];

    /**
     * Base math operators
     */
    public function __construct()
    {
        $this->addDefaults();
    }

    public function __clone()
    {
        $this->addDefaults();
    }

    /**
     * Get all vars
     *
     * @return array
     */
    public function getVars()
    {
        return $this->variables;
    }

    /**
     * Get a specific var
     *
     * @param  string $variable
     * @return integer|float
     * @throws UnknownVariableException
     */
    public function getVar($variable)
    {
        if (!isset($this->variables[$variable])) {
            throw new UnknownVariableException("Variable ({$variable}) not set");
        }

        return $this->variables[$variable];
    }

    /**
     * Add variable to executor
     *
     * @param  string $variable
     * @param  integer|float $value
     * @return MathExecutor
     * @throws \Exception
     */
    public function setVar($variable, $value)
    {
        if (!is_numeric($value)) {
            throw new \Exception("Variable ({$variable}) value must be a number ({$value}) type ({gettype($value)})");
        }

        $this->variables[$variable] = $value;

        return $this;
    }

    /**
     * Add variables to executor
     *
     * @param  array $variables
     * @param  bool $clear Clear previous variables
     * @return MathExecutor
     * @throws \Exception
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
     * @param  string $variable
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
        $this->variables = [];

        return $this;
    }

    /**
     * Add operator to executor
     *
     * @param  string $operatorClass Class of operator token
     * @return MathExecutor
     * @throws Exception\UnknownOperatorException
     */
    public function addOperator($operatorClass)
    {
        $this->tokenFactory->addOperator($operatorClass);

        return $this;
    }

    /**
     * Get all registered operators to executor
     *
     * @return array of operator class names
     */
    public function getOperators()
    {
        return $this->tokenFactory->getOperators();
    }

    /**
     * Add function to executor
     *
     * @param  string $name Name of function
     * @param  callable $function Function
     * @param  int $places Count of arguments
     * @return MathExecutor
     * @throws \ReflectionException
     */
    public function addFunction($name, $function = null, $places = null)
    {
        $this->tokenFactory->addFunction($name, $function, $places);

        return $this;
    }

    /**
     * Get all registered functions
     *
     * @return array containing callback and places indexed by
     *         function name
     */
    public function getFunctions()
    {
        return $this->tokenFactory->getFunctions();
    }

    /**
     * Set division by zero exception reporting
     *
     * @param bool $exception default true
     *
     * @return MathExecutor
     */
    public function setDivisionByZeroException($exception = true)
    {
        $this->tokenFactory->setDivisionByZeroException($exception);
        return $this;
    }

    /**
     * Get division by zero exception status
     *
     * @return bool
     */
    public function getDivisionByZeroException()
    {
        return $this->tokenFactory->getDivisionByZeroException();
    }

    /**
     * Execute expression
     *
     * @param $expression
     * @return number
     */
    public function execute($expression)
    {
        if (!array_key_exists($expression, $this->cache)) {
            $lexer = new Lexer($this->tokenFactory);
            $tokensStream = $lexer->stringToTokensStream($expression);
            $tokens = $lexer->buildReversePolishNotation($tokensStream);
            $this->cache[$expression] = $tokens;
        } else {
            $tokens = $this->cache[$expression];
        }
        $calculator = new Calculator();
        $result = $calculator->calculate($tokens, $this->variables);

        return $result;
    }

    /**
     * Set default operands and functions
     */
    protected function addDefaults()
    {
        $this->tokenFactory = new TokenFactory();

        foreach ($this->defaultOperators() as $operatorClass) {
            $this->tokenFactory->addOperator($operatorClass);
        }

        foreach ($this->defaultFunctions() as $name => $callable) {
            $this->tokenFactory->addFunction($name, $callable);
        }

        $this->setVars($this->defaultVars());
    }

    protected function defaultOperators()
    {
        return [
            'NXP\Classes\Token\TokenPlus',
            'NXP\Classes\Token\TokenMinus',
            'NXP\Classes\Token\TokenMultiply',
            'NXP\Classes\Token\TokenDivision',
            'NXP\Classes\Token\TokenDegree',
        ];
    }

    protected function defaultFunctions()
    {
        return [
            'sin' => function ($arg) {
                return sin($arg);
            },
            'cos' => function ($arg) {
                return cos($arg);
            },
            'tn' => function ($arg) {
                return tan($arg);
            },
            'asin' => function ($arg) {
                return asin($arg);
            },
            'acos' => function ($arg) {
                return acos($arg);
            },
            'atn' => function ($arg) {
                return atan($arg);
            },
            'min' => function ($arg1, $arg2) {
                return min($arg1, $arg2);
            },
            'max' => function ($arg1, $arg2) {
                return max($arg1, $arg2);
            },
            'avg' => function ($arg1, $arg2) {
                return ($arg1 + $arg2) / 2;
            },
        ];
    }

    protected function defaultVars()
    {
        return [
            'pi' => 3.14159265359,
            'e'  => 2.71828182846
        ];
    }
}
