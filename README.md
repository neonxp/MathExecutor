# MathExecutor [![Tests](https://github.com/neonxp/MathExecutor/workflows/Tests/badge.svg)](https://github.com/neonxp/MathExecutor/actions?query=workflow%3ATests) [![Latest Packagist release](https://img.shields.io/packagist/v/nxp/math-executor.svg)](https://packagist.org/packages/nxp/math-executor)

# A simple and extensible math expressions calculator

## Features:
* Built in support for +, -, *, / and power (^) operators plus ()
* Logical operators (==, !=, <, <, >=, <=, &&, ||)
* Built in support for most PHP math functions
* Conditional If logic
* Support for user defined operators
* Support for user defined functions
* Dynamic variable resolution (delayed computation)
* Unlimited variable name lengths
* String support, as function parameters or as evaluated as a number by PHP
* Exceptions on divide by zero, or treat as zero
* Unary Minus (e.g. -3)
* Pi ($pi) and Euler's number ($e) support to 11 decimal places
* Easily extensible

## Install via Composer:
```
composer require nxp/math-executor
```

## Sample usage:
```php
use NXP\MathExecutor;

$executor = new MathExecutor();

echo $executor->execute('1 + 2 * (2 - (4+10))^2 + sin(10)');
```

## Functions:
Default functions:
* abs
* acos
* acosh
* asin
* atan (atn)
* atan2
* atanh
* avg
* bindec
* ceil
* cos
* cosh
* decbin
* dechex
* decoct
* deg2rad
* exp
* expm1
* floor
* fmod
* hexdec
* hypot
* if
* intdiv
* log
* log10
* log1p
* max
* min
* octdec
* pi
* pow
* rad2deg
* round
* sin
* sinh
* sqrt
* tan (tn)
* tanh

Add custom function to executor:
```php
$executor->addFunction('abs', function($arg) {return abs($arg);});
```
Function default parameters are not supported at this time.

## Operators:
Default operators: `+ - * / ^`

Add custom operator to executor:

```php
use NXP\Classes\Operator;

$executor->addOperator(new Operator(
    '%', // Operator sign
    false, // Is right associated operator
    170, // Operator priority
    function (&$stack)
    {
       $op2 = array_pop($stack);
       $op1 = array_pop($stack);
       $result = $op1->getValue() % $op2->getValue();

       return $result;
    }
));
```

## Logical operators:
Logical operators (==, !=, <, <, >=, <=, &&, ||) are supported, but logically they can only return true (1) or false (0).  In order to leverage them, use the built in **if** function:

```
if($a > $b, $a - $b, $b - $a)
```

You can think of the **if** function as prototyped like:

```
function if($condition, $returnIfTrue, $returnIfFalse)
```
## Variables:
Default variables:

```
$pi = 3.14159265359
$e  = 2.71828182846
```

You can add your own variables to executor:

```php
$executor->setVar('var1', 0.15)->setVar('var2', 0.22);

echo $executor->execute("$var1 + $var2");
```

You can dynamically define variables at run time. If a variable has a high computation cost, but might not be used, then you can define an undefined variable handler. It will only get called when the variable is used, rather than having to always set it initially.

```php
$calculator = new MathExecutor();
$calculator->setVarNotFoundHandler(
    function ($varName) {
        if ($varName == 'trans') {
            return transmogrify();
        }
        return null;
    }
);
```

## Division By Zero Support:
Division by zero throws a `\NXP\Exception\DivisionByZeroException` by default
```php
try {
    echo $executor->execute('1/0');
} catch (DivisionByZeroException $e) {
    echo $e->getMessage();
}
```
Or call setDivisionByZeroIsZero
```php
echo $executor->setDivisionByZeroIsZero()->execute('1/0');
```
If you want another behavior, you can override division operator:
```php
$executor->addOperator("/", false, 180, function($a, $b) {
    if ($b == 0) {
        return null;
    }
    return $a / $b;
});
echo $executor->execute('1/0');
```

## Unary Minus Operator:
Negative numbers are supported via the unary minus operator. Positive numbers are not explicitly supported as unsigned numbers are assumed positive.

## String Support:
Expressions can contain double or single quoted strings that are evaluated the same way as PHP evalutes strings as numbers. You can also pass strings to functions.

```php
echo $executor->execute("1 + '2.5' * '.5' + myFunction('category')");
```

## Extending MathExecutor
You can add operators, functions and variables with the public methods in MathExecutor, but if you need to do more serious modifications to base behaviours, the easiest way to extend MathExecutor is to redefine the following methods in your derived class:
* defaultOperators
* defaultFunctions
* defaultVars

This will allow you to remove functions and operators if needed, or implement different types more simply.

Also note that you can replace an existing default operator by adding a new operator with the same regular expression string.  For example if you just need to redefine TokenPlus, you can just add a new operator with the same regex string, in this case '\\+'.

## Documentation

Full class documentation via [PHPFUI/InstaDoc](http://phpfui.com/?n=NXP&c=MathExecutor)

## Future Enhancements

This package will continue to track currently supported versions of PHP.  PHP 7.1 and earlier support will be dropped when PHP 8 is released.
