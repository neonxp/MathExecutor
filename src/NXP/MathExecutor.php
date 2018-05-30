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
     * Available variables
     *
     * @var array
     */
    private $variables = array();

    /**
     * middle results
     *
     * @var array
     */
    private $midresults = array();

    /**
     * @var TokenFactory
     */
    private $tokenFactory;

    /**
     * @var array
     */
    private $cache = array();

    /**
     * Base math operators
     */
    public function __construct()
    {
        $this->addDefaults();
        $this->addCustomFunc();
    }

    public function __clone()
    {
        $this->addDefaults();
        $this->addCustomFunc();
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
        $this->tokenFactory->addFunction('log10', 'log10', 1);
        $this->tokenFactory->addFunction('sqrt', 'sqrt', 1);
        $this->tokenFactory->addFunction('avg', function($arg1, $arg2) { return ($arg1 + $arg2) / 2; }, 2);

        $this->setVars(array(
            'pi' => 3.14159265359,
            'e'  => 2.71828182846
        ));
    }

    /**
     * Set custom functions
     */
    protected function addCustomFunc() {
        $this->tokenFactory->addFunction('log', function ($arg1, $arg2) { return log($arg2, $arg1); }, 2);
        $this->tokenFactory->addFunction('gt', function ($arg1, $arg2, $arg3, $arg4) {
            return $arg4 > $arg3 ? $arg2 : $arg1;
        }, 4);
        $this->tokenFactory->addFunction('egt', function ($arg1, $arg2, $arg3, $arg4) {
            return $arg4 >= $arg3 ? $arg2 : $arg1;
        }, 4);
        $this->tokenFactory->addFunction('lt', function ($arg1, $arg2, $arg3, $arg4) {
            return $arg4 < $arg3 ? $arg2 : $arg1;
        }, 4);
        $this->tokenFactory->addFunction('elt', function ($arg1, $arg2, $arg3, $arg4) {
            return $arg4 <= $arg3 ? $arg2 : $arg1;
        }, 4);
        $this->tokenFactory->addFunction('eq', function ($arg1, $arg2, $arg3, $arg4) {
            return $arg4 == $arg3 ? $arg2 : $arg1;
        }, 4);
        $this->addComplexFunc();
    }

    /**
     * Set complex custom functions
     */
    protected function addComplexFunc() {
        $this->tokenFactory->addFunction('lteach', function ($arg1, $arg2, $arg3, $arg4, $arg5, $arg6) {
            if($arg6 < $arg5) {
                return $arg4;
            } elseif($arg6 < $arg3) {
                return $arg2;
            } else {
                return $arg1;
            }
        }, 6);
        $this->tokenFactory->addFunction('elteach', function ($arg1, $arg2, $arg3, $arg4, $arg5, $arg6) {
            if($arg6 <= $arg5) {
                return $arg4;
            } elseif($arg6 <= $arg3) {
                return $arg2;
            } else {
                return $arg1;
            }
        }, 6);
        $this->tokenFactory->addFunction('mulgt', function ($arg1, $arg2, $arg3, $arg4, $arg5, $arg6, $arg7) {
            if($arg7 > $arg6) {
                if($arg5 > $arg4) {
                    return $arg3;
                } else {
                    return $arg2;
                }
            } else {
                return $arg1;
            }
        }, 7);
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
     * Add middle result to executor
     *
     * @param $midresult
     * @param $value
     */
    public function setMidResult($midresult, $value) {
        $this->midresults[$midresult] = $value;
    }

    /**
     * Add middle results to executor
     *
     * @param array $midresults
     * @param bool $clear
     * @return $this
     */
    public function setMidResults(array $midresults, $clear = true) {
        if ($clear) {
            $this->removeMidResults();
        }

        foreach($midresults as $name => $value) {
            $this->setMidResult($name, $value);
        }

        return $this;
    }

    /**
     * Remove variable from executor
     *
     * @param  string       $variable
     * @return MathExecutor
     */
    public function removeMidResult($variable)
    {
        unset ($this->midresults[$variable]);

        return $this;
    }

    /**
     * Remove all variables
     */
    public function removeMidResults()
    {
        $this->midresults = array();

        return $this;
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
     * @param  string       $name     Name of function
     * @param  callable     $function Function
     * @param  int          $places   Count of arguments
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
        $result = $calculator->calculate($tokens, $this->variables, $this->midresults, $this);

        return $result;
    }
}
