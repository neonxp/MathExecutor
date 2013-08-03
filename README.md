# NXP MathExecutor

Simple math expressions calculator

## Install via Composer

All instructions to install here: https://packagist.org/packages/nxp/math-executor

## Sample usage:

```php
require "vendor/autoload.php";

$calculator = new \NXP\MathExecutor();

print $calculator->execute("1 + 2 * (2 - (4+10))^2");
```

## Functions:

Default functions:
* sin
* cos
* tn
* asin
* asoc
* atn

Add custom function to executor:
```php
$executor->addFunction('abs', function($arg) {
    return abs($arg);
});
```

## Operators:

Default operators: `+ - * / ^`

## Variables:

You can add own variable to executor:

```php
$executor->setVars(array(
    'var1' => 0.15,
    'var2' => 0.22
));

$executor->execute("var1 + var2");