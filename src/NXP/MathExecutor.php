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
use NXP\Classes\Token;
use NXP\Classes\TokenFactory;

/**
 * Class MathExecutor
 * @package NXP
 */
class MathExecutor
{
    /**
     * @var TokenFactory
     */
    private $tokenFactory;

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
     * Set default operands and functions
     */
    protected function addDefaults()
    {
        $this->tokenFactory = new TokenFactory();

        $this->tokenFactory->addOperator('NXP\Classes\Token\TokenPlus');
        $this->tokenFactory->addOperator('NXP\Classes\Token\TokenMinus');
        $this->tokenFactory->addOperator('NXP\Classes\Token\TokenMultiply');
        $this->tokenFactory->addOperator('NXP\Classes\Token\TokenDivision');
        $this->tokenFactory->addOperator('NXP\Classes\Token\TokenDegree');

        $this->tokenFactory->addFunction('sin', 'sin');
        $this->tokenFactory->addFunction('cos', 'cos');
        $this->tokenFactory->addFunction('tn', 'tan');
        $this->tokenFactory->addFunction('asin', 'asin');
        $this->tokenFactory->addFunction('acos', 'acos');
        $this->tokenFactory->addFunction('atn', 'atan');
        $this->tokenFactory->addFunction('min', 'min', 2);
        $this->tokenFactory->addFunction('max', 'max', 2);
        $this->tokenFactory->addFunction('avg', function($arg1, $arg2) { return ($arg1 + $arg2) / 2; }, 2);

    }

    /**
     * Add operator to executor
     *
     * @param  string       $operatorClass Class of operator token
     * @return MathExecutor
     */
    public function addOperator($operatorClass)
    {
        $this->tokenFactory->addOperator($operatorClass);

        return $this;
    }

    /**
     * Add function to executor
     *
     * @param string   $name     Name of function
     * @param callable $function Function
     * @param int      $places   Count of arguments
     * @return MathExecutor
     */
    public function addFunction($name, callable $function = null, $places = 1)
    {
        $this->tokenFactory->addFunction($name, $function, $places);

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
        $lexer = new Lexer($this->tokenFactory);
        $tokensStream = $lexer->stringToTokensStream($expression);
        $tokens = $lexer->buildReversePolishNotation($tokensStream);
        $calculator = new Calculator();
        $result = $calculator->calculate($tokens);

        return $result;
    }
}
