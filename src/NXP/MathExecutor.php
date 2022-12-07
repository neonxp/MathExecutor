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
     * @return int|float|string|null
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
     * @param string        $name     Name of function
     * @param callable|null $function Function
     *
     * @throws ReflectionException
     * @throws Exception\IncorrectNumberOfFunctionParametersException
     */
    public function addFunction(string $name, ?callable $function = null) : self
    {
        $this->functions[$name] = new CustomFunction($name, $function);

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
     * @throws MathExecutorException if the value is invalid based on the default or custom validator
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
     */
    public function setVarValidationHandler(?callable $handler) : self
    {
        $this->onVarValidation = $handler;

        return $this;
    }

    /**
     * Remove variable from executor
     *
     */
    public function removeVar(string $variable) : self
    {
        unset($this->variables[$variable]);

        return $this;
    }

    /**
     * Remove all variables and the variable not found handler
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
    public function getOperators() : array
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
     * Remove a specific operator
     *
     * @return array<Operator> of operator class names
     */
    public function removeOperator(string $operator) : self
    {
        unset($this->operators[$operator]);

        return $this;
    }

    /**
     * Set division by zero returns zero instead of throwing DivisionByZeroException
     */
    public function setDivisionByZeroIsZero() : self
    {
        $this->addOperator(new Operator('/', false, 180, static fn ($a, $b) => 0 == $b ? 0 : $a / $b));

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
    public function clearCache() : self
    {
        $this->cache = [];

        return $this;
    }

    public function useBCMath(int $scale = 2) : self
    {
      \bcscale($scale);
      $this->addOperator(new Operator('+', false, 170, static fn ($a, $b) => \bcadd("{$a}", "{$b}")));
      $this->addOperator(new Operator('-', false, 170, static fn ($a, $b) => \bcsub("{$a}", "{$b}")));
      $this->addOperator(new Operator('uNeg', false, 200, static fn ($a) => \bcsub('0.0', "{$a}")));
      $this->addOperator(new Operator('*', false, 180, static fn ($a, $b) => \bcmul("{$a}", "{$b}")));
      $this->addOperator(new Operator('/', false, 180, static function($a, $b) {
          /** @todo PHP8: Use throw as expression -> static fn($a, $b) => 0 == $b ? throw new DivisionByZeroException() : $a / $b */
          if (0 == $b) {
              throw new DivisionByZeroException();
          }

          return \bcdiv("{$a}", "{$b}");
      }));
      $this->addOperator(new Operator('^', true, 220, static fn ($a, $b) => \bcpow("{$a}", "{$b}")));
      $this->addOperator(new Operator('%', false, 180, static fn ($a, $b) => \bcmod("{$a}", "{$b}")));

      return $this;
    }

    /**
     * Set default operands and functions
     * @throws ReflectionException
     */
    protected function addDefaults() : self
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

        return $this;
    }

    /**
     * Get the default operators
     *
     * @return array<string, array{callable, int, bool}>
     */
    protected function defaultOperators() : array
    {
        return [
          '+' => [static fn ($a, $b) => $a + $b, 170, false],
          '-' => [static fn ($a, $b) => $a - $b, 170, false],
          // unary positive token
          'uPos' => [static fn ($a) => $a, 200, false],
          // unary minus token
          'uNeg' => [static fn ($a) => 0 - $a, 200, false],
          '*' => [static fn ($a, $b) => $a * $b, 180, false],
          '/' => [
            static function($a, $b) { /** @todo PHP8: Use throw as expression -> static fn($a, $b) => 0 == $b ? throw new DivisionByZeroException() : $a / $b */
                if (0 == $b) {
                    throw new DivisionByZeroException();
                }

                return $a / $b;
            },
            180,
            false
          ],
          '^' => [static fn ($a, $b) => \pow($a, $b), 220, true],
          '%' => [static fn ($a, $b) => $a % $b, 180, false],
          '&&' => [static fn ($a, $b) => $a && $b, 100, false],
          '||' => [static fn ($a, $b) => $a || $b, 90, false],
          '==' => [static fn ($a, $b) => \is_string($a) || \is_string($b) ? 0 == \strcmp($a, $b) : $a == $b, 140, false],
          '!=' => [static fn ($a, $b) => \is_string($a) || \is_string($b) ? 0 != \strcmp($a, $b) : $a != $b, 140, false],
          '>=' => [static fn ($a, $b) => $a >= $b, 150, false],
          '>' => [static fn ($a, $b) => $a > $b, 150, false],
          '<=' => [static fn ($a, $b) => $a <= $b, 150, false],
          '<' => [static fn ($a, $b) => $a < $b, 150, false],
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
          'abs' => static fn ($arg) => \abs($arg),
          'acos' => static fn ($arg) => \acos($arg),
          'acosh' => static fn ($arg) => \acosh($arg),
          'arcsin' => static fn ($arg) => \asin($arg),
          'arcctg' => static fn ($arg) => M_PI / 2 - \atan($arg),
          'arccot' => static fn ($arg) => M_PI / 2 - \atan($arg),
          'arccotan' => static fn ($arg) => M_PI / 2 - \atan($arg),
          'arcsec' => static fn ($arg) => \acos(1 / $arg),
          'arccosec' => static fn ($arg) => \asin(1 / $arg),
          'arccsc' => static fn ($arg) => \asin(1 / $arg),
          'arccos' => static fn ($arg) => \acos($arg),
          'arctan' => static fn ($arg) => \atan($arg),
          'arctg' => static fn ($arg) => \atan($arg),
          'array' => static fn (...$args) => $args,
          'asin' => static fn ($arg) => \asin($arg),
          'atan' => static fn ($arg) => \atan($arg),
          'atan2' => static fn ($arg1, $arg2) => \atan2($arg1, $arg2),
          'atanh' => static fn ($arg) => \atanh($arg),
          'atn' => static fn ($arg) => \atan($arg),
          'avg' => static function($arg1, ...$args) {
              if (\is_array($arg1)){
                  if (0 === \count($arg1)){
                      throw new \InvalidArgumentException('avg() must have at least one argument!');
                  }

                  return \array_sum($arg1) / \count($arg1);
              }

              $args = [$arg1, ...$args];

              return \array_sum($args) / \count($args);
          },
          'bindec' => static fn ($arg) => \bindec($arg),
          'ceil' => static fn ($arg) => \ceil($arg),
          'cos' => static fn ($arg) => \cos($arg),
          'cosec' => static fn ($arg) => 1 / \sin($arg),
          'csc' => static fn ($arg) => 1 / \sin($arg),
          'cosh' => static fn ($arg) => \cosh($arg),
          'ctg' => static fn ($arg) => \cos($arg) / \sin($arg),
          'cot' => static fn ($arg) => \cos($arg) / \sin($arg),
          'cotan' => static fn ($arg) => \cos($arg) / \sin($arg),
          'cotg' => static fn ($arg) => \cos($arg) / \sin($arg),
          'ctn' => static fn ($arg) => \cos($arg) / \sin($arg),
          'decbin' => static fn ($arg) => \decbin($arg),
          'dechex' => static fn ($arg) => \dechex($arg),
          'decoct' => static fn ($arg) => \decoct($arg),
          'deg2rad' => static fn ($arg) => \deg2rad($arg),
          'exp' => static fn ($arg) => \exp($arg),
          'expm1' => static fn ($arg) => \expm1($arg),
          'floor' => static fn ($arg) => \floor($arg),
          'fmod' => static fn ($arg1, $arg2) => \fmod($arg1, $arg2),
          'hexdec' => static fn ($arg) => \hexdec($arg),
          'hypot' => static fn ($arg1, $arg2) => \hypot($arg1, $arg2),
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
          'intdiv' => static fn ($arg1, $arg2) => \intdiv($arg1, $arg2),
          'ln' => static fn ($arg) => \log($arg),
          'lg' => static fn ($arg) => \log10($arg),
          'log' => static fn ($arg) => \log($arg),
          'log10' => static fn ($arg) => \log10($arg),
          'log1p' => static fn ($arg) => \log1p($arg),
          'max' => static function($arg1, ...$args) {
              if (\is_array($arg1) && 0 === \count($arg1)){
                  throw new \InvalidArgumentException('max() must have at least one argument!');
              }

              return \max(\is_array($arg1) ? $arg1 : [$arg1, ...$args]);
          },
          'min' => static function($arg1, ...$args) {
              if (\is_array($arg1) && 0 === \count($arg1)){
                  throw new \InvalidArgumentException('min() must have at least one argument!');
              }

              return \min(\is_array($arg1) ? $arg1 : [$arg1, ...$args]);
          },
          'octdec' => static fn ($arg) => \octdec($arg),
          'pi' => static fn () => M_PI,
          'pow' => static fn ($arg1, $arg2) => $arg1 ** $arg2,
          'rad2deg' => static fn ($arg) => \rad2deg($arg),
          'round' => static fn ($num, int $precision = 0) => \round($num, $precision),
          'sin' => static fn ($arg) => \sin($arg),
          'sinh' => static fn ($arg) => \sinh($arg),
          'sec' => static fn ($arg) => 1 / \cos($arg),
          'sqrt' => static fn ($arg) => \sqrt($arg),
          'tan' => static fn ($arg) => \tan($arg),
          'tanh' => static fn ($arg) => \tanh($arg),
          'tn' => static fn ($arg) => \tan($arg),
          'tg' => static fn ($arg) => \tan($arg)
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
     * Default variable validation, ensures that the value is a scalar or array.
     * @throws MathExecutorException if the value is not a scalar
     */
    protected function defaultVarValidation(string $variable, $value) : void
    {
        if (! \is_scalar($value) && ! \is_array($value) && null !== $value) {
            $type = \gettype($value);

            throw new MathExecutorException("Variable ({$variable}) type ({$type}) is not scalar or array!");
        }
    }
}
