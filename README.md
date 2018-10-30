# MathExecutor [![Stories in Ready](https://badge.waffle.io/NeonXP/MathExecutor.png?label=ready&title=Ready)](https://waffle.io/NeonXP/MathExecutor) [![Build Status](https://travis-ci.org/NeonXP/MathExecutor.png?branch=master)](https://travis-ci.org/NeonXP/MathExecutor)

A simple math expressions calculator

## Features:
* Built in support for +, -, *, / and power (^) operators
* Support for user defined operators
* Support for user defined functions
* Unlimited varable length
* String support, as function parameters or as evaluated by PHP
* Exceptions on divide by zero, or treat as zero
* Unary Minus

## Install via Composer:
Stable branch
```
composer require "nxp/math-executor" "dev-master"
```

Dev branch (currently unsupported)
```
composer require "nxp/math-executor" "dev-dev"
```

## Sample usage:
```php
require "vendor/autoload.php";

$executor = new \NXP\MathExecutor();

echo $executor->execute("1 + 2 * (2 - (4+10))^2 + sin(10)");
```

## Functions:
Default functions:
* sin
* cos
* tn
* asin
* acos
* atn
* min
* max
* avg

Add custom function to executor:
```php
$executor->addFunction('abs', function($arg) {
    return abs($arg);
}, 1);
```

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
        return 3;
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

## Variables:
Default variables:

```
$pi = 3.14159265359
$e = 2.71828182846
```

You can add own variable to executor:

```php
$executor->setVars([
    'var1' => 0.15,
    'var2' => 0.22
]);

echo $executor->execute("$var1 + $var2");
```
## Division By Zero Support:
By default, the result of division by zero is zero and no error is generated.  You have the option to thow a \NXP\Exception\DivisionByZeroException by by calling setDivisionByZeroException.

```php
$executor->setDivisionByZeroException();
try {
    echo $executor->execute('1/0');
} catch (\NXP\Exception\DivisionByZeroException $e) {
    echo $e->getMessage();
}
```

## Unary Minus Operator:
Negative numbers are supported via the unary minus operator, but need to have a space before the minus sign. `-1+ -3` is legal, while '`-1+-3` will produce an error due to the way the parser works. Positive numbers are not explicitly supported as unsigned numbers are assumed positive.

## String Support:
Expressions can contain double or single quoted strings that are evaluated the same way as PHP. You can also pass strings to functions.

```php
echo $executor->execute("1 + '2.5' * '.5' + myFunction('category')");
```
