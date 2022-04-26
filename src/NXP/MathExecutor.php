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
use NXP\Classes\CustomFunction;
use NXP\Classes\Operator;
use NXP\Classes\Token;
use NXP\Classes\Tokenizer;
use NXP\Exception\DivisionByZeroException;
use NXP\Exception\MathExecutorException;
use NXP\Exception\UnknownVariableException;
use ReflectionException;

/**
 * Class MathExecutor
 * @package NXP
 */
class MathExecutor
{
    /**
     * Available variables
     *
     * @var array<string, float|string>
     */
    protected array $variables = [];

    /**
     * @var callable|null
     */
    protected $onVarNotFound = null;

    /**
     * @var callable|null
     */
    protected $onVarValidation = null;

    /**
     * @var Operator[]
     */
    protected array $operators = [];

    /**
     * @var array<string, CustomFunction>
     */
    protected array $functions = [];

    /**
     * @var array<string, Token[]>
     */
    protected array $cache = [];

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
     * Add operator to executor
     *
     * @return MathExecutor
     */
    public function addOperator(Operator $operator) : self
    {
        $this->operators[$operator->operator] = $operator;

        return $this;
    }

    /**
     * Execute expression
     *
     * @throws Exception\IncorrectBracketsException
     * @throws Exception\IncorrectExpressionException
     * @throws Exception\UnknownOperatorException
     * @throws UnknownVariableException
     * @return number
     */
    public function execute(string $expression, bool $cache = true)
    {
        $cacheKey = $expression;

        if (! \array_key_exists($cacheKey, $this->cache)) {
            $tokens = (new Tokenizer($expression, $this->operators))->tokenize()->buildReversePolishNotation();

            if ($cache) {
                $this->cache[$cacheKey] = $tokens;
            }
        } else {
            $tokens = $this->cache[$cacheKey];
        }

        $calculator = new Calculator($this->functions, $this->operators);

        return $calculator->calculate($tokens, $this->variables, $this->onVarNotFound);
    }

    /**
     * Add function to executor
     *
     * @param string $name Name of function
     * @param callable $function Function
     * @param int $places Count of arguments
     * @throws ReflectionException
     * @return MathExecutor
     */
    public function addFunction(string $name, ?callable $function = null, ?int $places = null) : self
    {
        $this->functions[$name] = new CustomFunction($name, $function, $places);

        return $this;
    }

    /**
     * Get all vars
     *
     * @return array<string, float|string>
     */
    public function getVars() : array
    {
        return $this->variables;
    }

    /**
     * Get a specific var
     *
     * @throws UnknownVariableException if VarNotFoundHandler is not set
     * @return int|float
     */
    public function getVar(string $variable)
    {
        if (! \array_key_exists($variable, $this->variables)) {
            if ($this->onVarNotFound) {
                return \call_user_func($this->onVarNotFound, $variable);
            }

            throw new UnknownVariableException("Variable ({$variable}) not set");
        }

        return $this->variables[$variable];
    }

    /**
     * Add variable to executor. To set a custom validator use setVarValidationHandler.
     *
     * @param  $value
     * @throws MathExecutorException if the value is invalid based on the default or custom validator
     * @return MathExecutor
     */
    public function setVar(string $variable, $value) : self
    {
        if ($this->onVarValidation) {
            \call_user_func($this->onVarValidation, $variable, $value);
        }

        $this->variables[$variable] = $value;

        return $this;
    }

    /**
     * Test to see if a variable exists
     *
     */
    public function varExists(string $variable) : bool
    {
        return \array_key_exists($variable, $this->variables);
    }

    /**
     * Add variables to executor
     *
     * @param  array<string, float|int|string> $variables
     * @param  bool $clear Clear previous variables
     * @throws \Exception
     * @return MathExecutor
     */
    public function setVars(array $variables, bool $clear = true) : self
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
     * Define a method that will be invoked when a variable is not found.
     * The first parameter will be the variable name, and the returned value will be used as the variable value.
     *
     *
     * @return MathExecutor
     */
    public function setVarNotFoundHandler(callable $handler) : self
    {
        $this->onVarNotFound = $handler;

        return $this;
    }

    /**
     * Define a validation method that will be invoked when a variable is set using setVar.
     * The first parameter will be the variable name, and the second will be the variable value.
     * Set to null to disable validation.
     *
     * @param ?callable $handler throws a MathExecutorException in case of an invalid variable
     *
     * @return MathExecutor
     */
    public function setVarValidationHandler(?callable $handler) : self
    {
        $this->onVarValidation = $handler;

        return $this;
    }

    /**
     * Remove variable from executor
     *
     * @return MathExecutor
     */
    public function removeVar(string $variable) : self
    {
        unset($this->variables[$variable]);

        return $this;
    }

    /**
     * Remove all variables and the variable not found handler
     * @return MathExecutor
     */
    public function removeVars() : self
    {
        $this->variables = [];
        $this->onVarNotFound = null;

        return $this;
    }

    /**
     * Get all registered operators to executor
     *
     * @return array<Operator> of operator class names
     */
    public function getOperators()
    {
        return $this->operators;
    }

    /**
     * Get all registered functions
     *
     * @return array<string, CustomFunction> containing callback and places indexed by
     *         function name
     */
    public function getFunctions() : array
    {
        return $this->functions;
    }

    /**
     * Set division by zero returns zero instead of throwing DivisionByZeroException
     *
     * @return MathExecutor
     */
    public function setDivisionByZeroIsZero() : self
    {
        $this->addOperator(new Operator('/', false, 180, static function($a, $b) {
            if (0 == $b) {
                return 0;
            }

            return $a / $b;
        }));

        return $this;
    }

    /**
     * Get cache array with tokens
     * @return array<string, Token[]>
     */
    public function getCache() : array
    {
        return $this->cache;
    }

    /**
     * Clear token's cache
     */
    public function clearCache() : void
    {
        $this->cache = [];
    }

    /**
     * Set default operands and functions
     * @throws ReflectionException
     */
    protected function addDefaults() : void
    {
        foreach ($this->defaultOperators() as $name => $operator) {
            [$callable, $priority, $isRightAssoc] = $operator;
            $this->addOperator(new Operator($name, $isRightAssoc, $priority, $callable));
        }

        foreach ($this->defaultFunctions() as $name => $callable) {
            $this->addFunction($name, $callable);
        }

        $this->onVarValidation = [$this, 'defaultVarValidation'];
        $this->variables = $this->defaultVars();
    }

    /**
     * Get the default operators
     *
     * @return array<string, array{callable, int, bool}>
     */
    protected function defaultOperators() : array
    {
        return [
          '+' => [
            static function($a, $b) {
                return $a + $b;
            },
            170,
            false
          ],
          '-' => [
            static function($a, $b) {
                return $a - $b;
            },
            170,
            false
          ],
          'uPos' => [ // unary positive token
            static function($a) {
                return $a;
            },
            200,
            false
          ],
          'uNeg' => [ // unary minus token
            static function($a) {
                return 0 - $a;
            },
            200,
            false
          ],
          '*' => [
            static function($a, $b) {
                return $a * $b;
            },
            180,
            false
          ],
          '/' => [
            static function($a, $b) {
                if (0 == $b) {
                    throw new DivisionByZeroException();
                }

                return $a / $b;
            },
            180,
            false
          ],
          '^' => [
            static function($a, $b) {
                return \pow($a, $b);
            },
            220,
            true
          ],
          '&&' => [
            static function($a, $b) {
                return $a && $b;
            },
            100,
            false
          ],
          '||' => [
            static function($a, $b) {
                return $a || $b;
            },
            90,
            false
          ],
          '==' => [
            static function($a, $b) {
                if (\is_string($a) || \is_string($b)) {
                    return 0 == \strcmp($a, $b);
                }

                    return $a == $b;

            },
            140,
            false
          ],
          '!=' => [
            static function($a, $b) {
                if (\is_string($a) || \is_string($b)) {
                    return 0 != \strcmp($a, $b);
                }

                    return $a != $b;

            },
            140,
            false
          ],
          '>=' => [
            static function($a, $b) {
                return $a >= $b;
            },
            150,
            false
          ],
          '>' => [
            static function($a, $b) {
                return $a > $b;
            },
            150,
            false
          ],
          '<=' => [
            static function($a, $b) {
                return $a <= $b;
            },
            150,
            false
          ],
          '<' => [
            static function($a, $b) {
                return $a < $b;
            },
            150,
            false
          ],
        ];
    }

    /**
     * Gets the default functions as an array.  Key is function name
     * and value is the function as a closure.
     *
     * @return array<callable>
     */
    protected function defaultFunctions() : array
    {
        return [
          'abs' => static function($arg) {
              return \abs($arg);
          },
          'acos' => static function($arg) {
              return \acos($arg);
          },
          'acosh' => static function($arg) {
              return \acosh($arg);
          },
          'arcsin' => static function($arg) {
              return \asin($arg);
          },
          'arcctg' => static function($arg) {
              return M_PI / 2 - \atan($arg);
          },
          'arccot' => static function($arg) {
              return M_PI / 2 - \atan($arg);
          },
          'arccotan' => static function($arg) {
              return M_PI / 2 - \atan($arg);
          },
          'arcsec' => static function($arg) {
              return \acos(1 / $arg);
          },
          'arccosec' => static function($arg) {
              return \asin(1 / $arg);
          },
          'arccsc' => static function($arg) {
              return \asin(1 / $arg);
          },
          'arccos' => static function($arg) {
              return \acos($arg);
          },
          'arctan' => static function($arg) {
              return \atan($arg);
          },
          'arctg' => static function($arg) {
              return \atan($arg);
          },
          'asin' => static function($arg) {
              return \asin($arg);
          },
          'atan' => static function($arg) {
              return \atan($arg);
          },
          'atan2' => static function($arg1, $arg2) {
              return \atan2($arg1, $arg2);
          },
          'atanh' => static function($arg) {
              return \atanh($arg);
          },
          'atn' => static function($arg) {
              return \atan($arg);
          },
          'avg' => static function($arg1, $arg2) {
              return ($arg1 + $arg2) / 2;
          },
          'bindec' => static function($arg) {
              return \bindec($arg);
          },
          'ceil' => static function($arg) {
              return \ceil($arg);
          },
          'cos' => static function($arg) {
              return \cos($arg);
          },
          'cosec' => static function($arg) {
              return 1 / \sin($arg);
          },
          'csc' => static function($arg) {
              return 1 / \sin($arg);
          },
          'cosh' => static function($arg) {
              return \cosh($arg);
          },
          'ctg' => static function($arg) {
              return \cos($arg) / \sin($arg);
          },
          'cot' => static function($arg) {
              return \cos($arg) / \sin($arg);
          },
          'cotan' => static function($arg) {
              return \cos($arg) / \sin($arg);
          },
          'cotg' => static function($arg) {
              return \cos($arg) / \sin($arg);
          },
          'ctn' => static function($arg) {
              return \cos($arg) / \sin($arg);
          },
          'decbin' => static function($arg) {
              return \decbin($arg);
          },
          'dechex' => static function($arg) {
              return \dechex($arg);
          },
          'decoct' => static function($arg) {
              return \decoct($arg);
          },
          'deg2rad' => static function($arg) {
              return \deg2rad($arg);
          },
          'exp' => static function($arg) {
              return \exp($arg);
          },
          'expm1' => static function($arg) {
              return \expm1($arg);
          },
          'floor' => static function($arg) {
              return \floor($arg);
          },
          'fmod' => static function($arg1, $arg2) {
              return \fmod($arg1, $arg2);
          },
          'hexdec' => static function($arg) {
              return \hexdec($arg);
          },
          'hypot' => static function($arg1, $arg2) {
              return \hypot($arg1, $arg2);
          },
          'if' => function($expr, $trueval, $falseval) {
              if (true === $expr || false === $expr) {
                  $exres = $expr;
              } else {
                  $exres = $this->execute($expr);
              }

              if ($exres) {
                  return $this->execute($trueval);
              }

                  return $this->execute($falseval);

          },
          'intdiv' => static function($arg1, $arg2) {
              return \intdiv($arg1, $arg2);
          },
          'ln' => static function($arg) {
              return \log($arg);
          },
          'lg' => static function($arg) {
              return \log10($arg);
          },
          'log' => static function($arg) {
              return \log($arg);
          },
          'log10' => static function($arg) {
              return \log10($arg);
          },
          'log1p' => static function($arg) {
              return \log1p($arg);
          },
          'max' => static function($arg1, $arg2) {
              return \max($arg1, $arg2);
          },
          'min' => static function($arg1, $arg2) {
              return \min($arg1, $arg2);
          },
          'octdec' => static function($arg) {
              return \octdec($arg);
          },
          'pi' => static function() {
              return M_PI;
          },
          'pow' => static function($arg1, $arg2) {
              return $arg1 ** $arg2;
          },
          'rad2deg' => static function($arg) {
              return \rad2deg($arg);
          },
          'round' => static function($arg) {
              return \round($arg);
          },
          'sin' => static function($arg) {
              return \sin($arg);
          },
          'sinh' => static function($arg) {
              return \sinh($arg);
          },
          'sec' => static function($arg) {
              return 1 / \cos($arg);
          },
          'sqrt' => static function($arg) {
              return \sqrt($arg);
          },
          'tan' => static function($arg) {
              return \tan($arg);
          },
          'tanh' => static function($arg) {
              return \tanh($arg);
          },
          'tn' => static function($arg) {
              return \tan($arg);
          },
          'tg' => static function($arg) {
              return \tan($arg);
          }
        ];
    }

    /**
     * Returns the default variables names as key/value pairs
     *
     * @return array<string, float>
     */
    protected function defaultVars() : array
    {
        return [
          'pi' => 3.14159265359,
          'e' => 2.71828182846
        ];
    }

    /**
     * Default variable validation, ensures that the value is a scalar.
     * @param $value
     * @throws MathExecutorException if the value is not a scalar
     */
    protected function defaultVarValidation(string $variable, $value) : void
    {
        if (! \is_scalar($value) && null !== $value) {
            $type = \gettype($value);

            throw new MathExecutorException("Variable ({$variable}) type ({$type}) is not scalar");
        }
    }
}
