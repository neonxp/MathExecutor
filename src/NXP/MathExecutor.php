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
     * @return MathExecutor
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
        $cachekey = (string)$expression;
        if (!array_key_exists($cachekey, $this->cache)) {
            $lexer = new Lexer($this->tokenFactory);
            $tokensStream = $lexer->stringToTokensStream($expression);
            $tokens = $lexer->buildReversePolishNotation($tokensStream);
            $this->cache[$cachekey] = $tokens;
        } else {
            $tokens = $this->cache[$cachekey];
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

    /**
     * Get the default operators
     *
     * @return array of class names
     */
    protected function defaultOperators()
    {
        return [
            \NXP\Classes\Token\TokenPlus::class,
            \NXP\Classes\Token\TokenMinus::class,
            \NXP\Classes\Token\TokenMultiply::class,
            \NXP\Classes\Token\TokenDivision::class,
            \NXP\Classes\Token\TokenDegree::class,
            \NXP\Classes\Token\TokenAnd::class,
            \NXP\Classes\Token\TokenOr::class,
            \NXP\Classes\Token\TokenEqual::class,
            \NXP\Classes\Token\TokenNotEqual::class,
            \NXP\Classes\Token\TokenGreaterThanOrEqual::class,
            \NXP\Classes\Token\TokenGreaterThan::class,
            \NXP\Classes\Token\TokenLessThanOrEqual::class,
            \NXP\Classes\Token\TokenLessThan::class,
        ];
    }

    /**
     * Gets the default functions as an array.  Key is function name
     * and value is the function as a closure.
     *
     * @return array
     */
    protected function defaultFunctions()
    {
        return [
            'abs' => function ($arg) {
                return abs($arg);
            },
            'acos' => function ($arg) {
                return acos($arg);
            },
            'acosh' => function ($arg) {
                return acosh($arg);
            },
            'asin' => function ($arg) {
                return asin($arg);
            },
            'atan' => function ($arg) {
                return atan($arg);
            },
            'atan2' => function ($arg1, $arg2) {
                return atan2($arg1, $arg2);
            },
            'atanh' => function ($arg) {
                return atanh($arg);
            },
            'atn' => function ($arg) {
                return atan($arg);
            },
            'avg' => function ($arg1, $arg2) {
                return ($arg1 + $arg2) / 2;
            },
            'bindec' => function ($arg) {
                return bindec($arg);
            },
            'ceil' => function ($arg) {
                return ceil($arg);
            },
            'cos' => function ($arg) {
                return cos($arg);
            },
            'cosh' => function ($arg) {
                return cosh($arg);
            },
            'decbin' => function ($arg) {
                return decbin($arg);
            },
            'dechex' => function ($arg) {
                return dechex($arg);
            },
            'decoct' => function ($arg) {
                return decoct($arg);
            },
            'deg2rad' => function ($arg) {
                return deg2rad($arg);
            },
            'exp' => function ($arg) {
                return exp($arg);
            },
            'expm1' => function ($arg) {
                return expm1($arg);
            },
            'floor' => function ($arg) {
                return floor($arg);
            },
            'fmod' => function ($arg1, $arg2) {
                return fmod($arg1, $arg2);
            },
            'hexdec' => function ($arg) {
                return hexdec($arg);
            },
            'hypot' => function ($arg1, $arg2) {
                return hypot($arg1, $arg2);
            },
            'if' => function ($expr, $trueval, $falseval) {
                if ($expr === true || $expr === false) {
                    $exres = $expr;
                } else {
                    $exres = $this->execute($expr);
                }
                if ($exres) {
                    return $this->execute($trueval);
                } else {
                    return $this->execute($falseval);
                }
            },
            'intdiv' => function ($arg1, $arg2) {
                return intdiv($arg1, $arg2);
            },
            'log' => function ($arg) {
                return log($arg);
            },
            'log10' => function ($arg) {
                return log10($arg);
            },
            'log1p' => function ($arg) {
                return log1p($arg);
            },
            'max' => function ($arg1, $arg2) {
                return max($arg1, $arg2);
            },
            'min' => function ($arg1, $arg2) {
                return min($arg1, $arg2);
            },
            'octdec' => function ($arg) {
                return octdec($arg);
            },
            'pi' => function () {
                return pi();
            },
            'pow' => function ($arg1, $arg2) {
                return pow($arg1, $arg2);
            },
            'rad2deg' => function ($arg) {
                return rad2deg($arg);
            },
            'round' => function ($arg) {
                return round($arg);
            },
            'sin' => function ($arg) {
                return sin($arg);
            },
            'sinh' => function ($arg) {
                return sinh($arg);
            },
            'sqrt' => function ($arg) {
                return sqrt($arg);
            },
            'tan' => function ($arg) {
                return tan($arg);
            },
            'tanh' => function ($arg) {
                return tanh($arg);
            },
            'tn' => function ($arg) {
                return tan($arg);
            }
        ];
    }

    /**
     * Returns the default variables names as key/value pairs
     *
     * @return array
     */
    protected function defaultVars()
    {
        return [
            'pi' => 3.14159265359,
            'e'  => 2.71828182846
        ];
    }
}
