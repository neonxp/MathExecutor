# MathExecutor [![Tests](https://github.com/neonxp/MathExecutor/workflows/Tests/badge.svg)](https://github.com/neonxp/MathExecutor/actions?query=workflow%3ATests)

# A simple and extensible math expressions calculator

## Features:
* Built in support for +, -, *, %, / and power (^) operators
* Paratheses () and arrays [] are fully supported
* Logical operators (==, !=, <, <, >=, <=, &&, ||)
* Built in support for most PHP math functions
* Support for variable number of function parameters and optional function parameters
* Conditional If logic
* Support for user defined operators
* Support for user defined functions
* Support for math on user defined objects
* Dynamic variable resolution (delayed computation)
* Unlimited variable name lengths
* String support, as function parameters or as evaluated as a number by PHP
* Exceptions on divide by zero, or treat as zero
* Unary Plus and Minus (e.g. +3 or -sin(12))
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
* acos (arccos)
* acosh
* arcctg (arccot, arccotan)
* arcsec
* arccsc (arccosec)
* array
* asin (arcsin)
* atan (atn, arctan, arctg)
* atan2
* atanh
* avg
* bindec
* ceil
* cos
* cosec (csc)
* cosh
* ctg (cot, cotan, cotg, ctn)
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
* log (ln)
* log10 (lg)
* log1p
* max
* min
* octdec
* pi
* pow
* rad2deg
* round
* sec
* sin
* sinh
* sqrt
* tan (tn, tg)
* tanh

Add custom function to executor:
```php
$executor->addFunction('concat', function($arg1, $arg2) {return $arg1 . $arg2;});
```
Optional parameters:
```php
$executor->addFunction('round', function($num, int $precision = 0) {return round($num, $precision);});
$executor->calculate('round(17.119)'); // 17
$executor->calculate('round(17.119, 2)'); // 17.12
```
Variable number of parameters:
```php
$executor->addFunction('avarage', function(...$args) {return array_sum($args) / count($args);});
$executor->calculate('avarage(1,3)'); // 2
$executor->calculate('avarage(1, 3, 4, 8)'); // 4
```

## Operators:
Default operators: `+ - * / % ^`

Add custom operator to executor:

```php
use NXP\Classes\Operator;

$executor->addOperator(new Operator(
    '%', // Operator sign
    false, // Is right associated operator
    180, // Operator priority
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
Variables can be prefixed with the dollar sign ($) for PHP compatibility, but is not required.

Default variables:

```
$pi = 3.14159265359
$e  = 2.71828182846
```

You can add your own variables to executor:

```php
$executor->setVar('var1', 0.15)->setVar('var2', 0.22);

echo $executor->execute("$var1 + var2");
```

Arrays are also supported (as variables, as func params or can be returned in user defined funcs):
```php
$executor->setVar('monthly_salaries', [1800, 1900, 1200, 1600]);

echo $executor->execute("avg(monthly_salaries) * min([1.1, 1.3])");
```

By default, variables must be scalar values (int, float, bool or string) or array.  If you would like to support another type, use **setVarValidationHandler**

```php
$executor->setVarValidationHandler(function (string $name, $variable) {
    // allow all scalars, array and null
    if (is_scalar($variable) || is_array($variable) || $variable === null) {
        return;
    }
    // Allow variables of type DateTime, but not others
    if (! $variable instanceof \DateTime) {
        throw new MathExecutorException("Invalid variable type");
    }
});
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

## Floating Point BCMath Support
By default, `MathExecutor` uses PHP floating point math, but if you need a fixed precision, call **useBCMath()**. Precision defaults to 2 decimal points, or pass the required number.
`WARNING`: Functions may return a PHP floating point number.  By doing the basic math functions on the results, you will get back a fixed number of decimal points. Use a plus sign in from of any stand alone function to return the proper number of decimal places.

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

## String Support:
Expressions can contain double or single quoted strings that are evaluated the same way as PHP evaluates strings as numbers. You can also pass strings to functions.

```php
echo $executor->execute("1 + '2.5' * '.5' + myFunction('category')");
```
To use reverse solidus character (&#92;) in strings, or to use single quote character (') in a single quoted string, or to use double quote character (") in a double quoted string, you must prepend reverse solidus character (&#92;).

```php
echo $executor->execute("countArticleSentences('My Best Article\'s Title')");
```

## Extending MathExecutor
You can add operators, functions and variables with the public methods in MathExecutor, but if you need to do more serious modifications to base behaviors, the easiest way to extend MathExecutor is to redefine the following methods in your derived class:
* defaultOperators
* defaultFunctions
* defaultVars

This will allow you to remove functions and operators if needed, or implement different types more simply.

Also note that you can replace an existing default operator by adding a new operator with the same regular expression string.  For example if you just need to redefine TokenPlus, you can just add a new operator with the same regex string, in this case '\\+'.

## Documentation

Full class documentation via [PHPFUI/InstaDoc](http://phpfui.com/?n=NXP&c=MathExecutor)

## Future Enhancements

This package will continue to track currently supported versions of PHP.
