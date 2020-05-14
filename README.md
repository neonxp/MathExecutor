# MathExecutor [![Build Status](https://travis-ci.org/NeonXP/MathExecutor.png?branch=master)](https://travis-ci.org/NeonXP/MathExecutor)

# A simple and extensible math expressions calculator

## Features:
* Built in support for +, -, *, / and power (^) operators plus ()
* Logical operators (==, !=, <, <, >=, <=, &&, ||)
* Built in support for most PHP math functions
* Conditional If logic
* Support for user defined operators
* Support for user defined functions
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
require 'vendor/autoload.php';

$executor = new \NXP\MathExecutor();

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
<?php
namespace MyNamespace;

use NXP\Classes\Token\AbstractOperator;

class ModulusToken extends AbstractOperator
{
    /**
     * Regex of this operator
     * @return string
     */
    public static function getRegex()
    {
        return '\%';
    }

    /**
     * Priority of this operator
     * @return int
     */
    public function getPriority()
    {
        return 180;
    }

    /**
     * Associaion of this operator (self::LEFT_ASSOC or self::RIGHT_ASSOC)
     * @return string
     */
    public function getAssociation()
    {
        return self::LEFT_ASSOC;
    }

    /**
     * Execution of this operator
     * @param InterfaceToken[] $stack Stack of tokens
     * @return TokenNumber            Result of execution
     */
    public function execute(&$stack)
    {
        $op2 = array_pop($stack);
        $op1 = array_pop($stack);
        $result = $op1->getValue() % $op2->getValue();

        return new TokenNumber($result);
    }
}
```

And adding to executor:

```php
$executor->addOperator('MyNamespace\ModulusToken');
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
$executor->setVars([
    'var1' => 0.15,
    'var2' => 0.22
]);

echo $executor->execute("$var1 + $var2");
```
## Division By Zero Support:
By default, the result of division by zero is zero and no error is generated.  You have the option to throw a `\NXP\Exception\DivisionByZeroException` by calling `setDivisionByZeroException`.

```php
$executor->setDivisionByZeroException();
try {
    echo $executor->execute('1/0');
} catch (\NXP\Exception\DivisionByZeroException $e) {
    echo $e->getMessage();
}
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
