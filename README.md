[![Stories in Ready](https://badge.waffle.io/NeonXP/MathExecutor.png?label=ready&title=Ready)](https://waffle.io/NeonXP/MathExecutor)
# MathExecutor

[![Build Status](https://travis-ci.org/NeonXP/MathExecutor.png?branch=master)](https://travis-ci.org/NeonXP/MathExecutor)

Simple math expressions calculator

## Install via Composer

All instructions to install here: https://packagist.org/packages/nxp/math-executor

## Sample usage:

```php
require "vendor/autoload.php";

$calculator = new \NXP\MathExecutor();

print $calculator->execute("1 + 2 * (2 - (4+10))^2 + sin(10)");
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

MyNamespace/ModulusToken.php:

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
$executor->setVars(array(
    'var1' => 0.15,
    'var2' => 0.22
));

$executor->execute("$var1 + $var2");
