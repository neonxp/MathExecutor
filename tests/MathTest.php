<?php

/**
 * This file is part of the MathExecutor package
 *
 * (c) Alexander Kiryukhin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace NXP\Tests;

use Exception;
use NXP\Exception\DivisionByZeroException;
use NXP\Exception\IncorrectExpressionException;
use NXP\Exception\IncorrectNumberOfFunctionParametersException;
use NXP\Exception\MathExecutorException;
use NXP\Exception\UnknownFunctionException;
use NXP\Exception\UnknownVariableException;
use NXP\MathExecutor;
use PHPUnit\Framework\TestCase;

class MathTest extends TestCase
{
    /**
     * @dataProvider providerExpressions
     */
    public function testCalculating(string $expression) : void
    {
        $calculator = new MathExecutor();

        /** @var float $phpResult */
        $phpResult = 0.0;
        eval('$phpResult = ' . $expression . ';');

        try {
            $result = $calculator->execute($expression);
        } catch (Exception $e) {
            $this->fail(\sprintf('Exception: %s (%s:%d), expression was: %s', $e::class, $e->getFile(), $e->getLine(), $expression));
        }
        $this->assertEquals($phpResult, $result, "Expression was: {$expression}");
    }

    /**
     * Expressions data provider
     *
     * Most tests can go in here.  The idea is that each expression will be evaluated by MathExecutor and by PHP with eval.
     * The results should be the same.  If they are not, then the test fails.  No need to add extra test unless you are doing
     * something more complex and not a simple mathmatical expression.
     *
     * @return array<array<string>>
     */
    public function providerExpressions()
    {
        return [
          ['-5'],
          ['-5+10'],
          ['4-5'],
          ['4 -5'],
          ['(4*2)-5'],
          ['(4*2) - 5'],
          ['4*-5'],
          ['4 * -5'],
          ['+5'],
          ['+(3+2)'],
          ['+(+3+2)'],
          ['+(-3+2)'],
          ['-5'],
          ['-(-5)'],
          ['-(+5)'],
          ['+(-5)'],
          ['+(+5)'],
          ['-(3+2)'],
          ['-(-3+-2)'],

          ['abs(1.5)'],
          ['acos(0.15)'],
          ['acosh(1.5)'],
          ['asin(0.15)'],
          ['atan(0.15)'],
          ['atan2(1.5, 3.5)'],
          ['atanh(0.15)'],
          ['bindec("10101")'],
          ['ceil(1.5)'],
          ['cos(1.5)'],
          ['cosh(1.5)'],
          ['decbin("15")'],
          ['dechex("15")'],
          ['decoct("15")'],
          ['deg2rad(1.5)'],
          ['exp(1.5)'],
          ['expm1(1.5)'],
          ['floor(1.5)'],
          ['fmod(1.5, 3.5)'],
          ['hexdec("abcdef")'],
          ['hypot(1.5, 3.5)'],
          ['intdiv(10, 2)'],
          ['log(1.5)'],
          ['log10(1.5)'],
          ['log1p(1.5)'],
          ['max(1.5, 3.5)'],
          ['min(1.5, 3.5)'],
          ['octdec("15")'],
          ['pi()'],
          ['pow(1.5, 3.5)'],
          ['rad2deg(1.5)'],
          ['round(1.5)'],
          ['sin(1.5)'],
          ['sin(12)'],
          ['+sin(12)'],
          ['-sin(12)'],
          ['sinh(1.5)'],
          ['sqrt(1.5)'],
          ['tan(1.5)'],
          ['tanh(1.5)'],

          ['0.1 + 0.2'],
          ['0.1 + 0.2 - 0.3'],
          ['1 + 2'],

          ['0.1 - 0.2'],
          ['1 - 2'],

          ['0.1 * 2'],
          ['1 * 2'],

          ['0.1 / 0.2'],
          ['1 / 2'],

          ['2 * 2 + 3 * 3'],
          ['2 * 2 / 3 * 3'],
          ['2 / 2 / 3 / 3'],
          ['2 / 2 * 3 / 3'],
          ['2 / 2 * 3 * 3'],

          ['1 + 0.6 - 3 * 2 / 50'],

          ['(5 + 3) * -1'],

          ['-2- 2*2'],
          ['2- 2*2'],
          ['2-(2*2)'],
          ['(2- 2)*2'],
          ['2 + 2*2'],
          ['2+ 2*2'],
          ['2+2*2'],
          ['(2+2)*2'],
          ['(2 + 2)*-2'],
          ['(2+-2)*2'],

          ['1 + 2 * 3 / (min(1, 5) + 2 + 1)'],
          ['1 + 2 * 3 / (min(1, 5) - 2 + 5)'],
          ['1 + 2 * 3 / (min(1, 5) * 2 + 1)'],
          ['1 + 2 * 3 / (min(1, 5) / 2 + 1)'],
          ['1 + 2 * 3 / (min(1, 5) / 2 * 1)'],
          ['1 + 2 * 3 / (min(1, 5) / 2 / 1)'],
          ['1 + 2 * 3 / (3 + min(1, 5) + 2 + 1)'],
          ['1 + 2 * 3 / (3 - min(1, 5) - 2 + 1)'],
          ['1 + 2 * 3 / (3 * min(1, 5) * 2 + 1)'],
          ['1 + 2 * 3 / (3 / min(1, 5) / 2 + 1)'],

          ['(1 + 2) * 3 / (3 / min(1, 5) / 2 + 1)'],

          ['sin(10) * cos(50) / min(10, 20/2)'],
          ['sin(10) * cos(50) / min(10, (20/2))'],
          ['sin(10) * cos(50) / min(10, (max(10,20)/2))'],

          ['100500 * 3.5e5'],
          ['100500 * 3.5e-5'],
          ['100500 * 3.5E5'],
          ['100500 * 3.5E-5'],

          ['1 + "2" / 3'],
          ["1.5 + '2.5' / 4"],
          ['1.5 + "2.5" * ".5"'],

          ['-1 + -2'],
          ['-1+-2'],
          ['-1- -2'],
          ['-1/-2'],
          ['-1*-2'],

          ['(1+2+3+4-5)*7/100'],
          ['(-1+2+3+4- 5)*7/100'],
          ['(1+2+3+4- 5)*7/100'],
          ['( 1 + 2 + 3 + 4 - 5 ) * 7 / 100'],

          ['1 && 0'],
          ['1 && 0 && 1'],
          ['1 || 0'],
          ['1 && 0 || 1'],

          ['5 == 3'],
          ['5 == 5'],
          ['5 != 3'],
          ['5 != 5'],
          ['5 > 3'],
          ['3 > 5'],
          ['3 >= 5'],
          ['3 >= 3'],
          ['3 < 5'],
          ['5 < 3'],
          ['3 <= 5'],
          ['5 <= 5'],
          ['10 < 9 || 4 > (2+1)'],
          ['10 < 9 || 4 > (-2+1)'],
          ['10 < 9 || 4 > (2+1) && 5 == 5 || 4 != 6 || 3 >= 4 || 3 <= 7'],

          ['1 + 5 == 3 + 1'],
          ['1 + 5 == 5 + 1'],
          ['1 + 5 != 3 + 1'],
          ['1 + 5 != 5 + 1'],
          ['1 + 5 > 3 + 1'],
          ['1 + 3 > 5 + 1'],
          ['1 + 3 >= 5 + 1'],
          ['1 + 3 >= 3 + 1'],
          ['1 + 3 < 5 + 1'],
          ['1 + 5 < 3 + 1'],
          ['1 + 3 <= 5 + 1'],
          ['1 + 5 <= 5 + 1'],

          ['(-4)'],
          ['(-4 + 5)'],
          ['(3 * 1)'],
          ['(-3 * -1)'],
          ['1 + (-3 * -1)'],
          ['1 + ( -3 * 1)'],
          ['1 + (3 *-1)'],
          ['1 - 0'],
          ['1-0'],

          ['-(1.5)'],
          ['-log(4)'],
          ['0-acosh(1.5)'],
          ['-acosh(1.5)'],
          ['-(-4)'],
          ['-(-4 + 5)'],
          ['-(3 * 1)'],
          ['-(-3 * -1)'],
          ['-1 + (-3 * -1)'],
          ['-1 + ( -3 * 1)'],
          ['-1 + (3 *-1)'],
          ['-1 - 0'],
          ['-1-0'],
          ['-(4*2)-5'],
          ['-(4*-2)-5'],
          ['-(-4*2) - 5'],
          ['-4*-5'],
          ['max(1,2,4.9,3)'],
          ['min(1,2,4.9,3)'],
          ['max([1,2,4.9,3])'],
          ['min([1,2,4.9,3])'],

          ['4 % 4'],
          ['7 % 4'],
          ['99 % 4'],
          ['123 % 7'],
          ['!(1||0)'],
          ['!(1&&0)'],
        ];
    }

    /**
     * @dataProvider bcMathExpressions
     */
    public function testBCMathCalculating(string $expression, string $expected = '') : void
    {
        $calculator = new MathExecutor();
        $calculator->useBCMath();

        if ('' === $expected)
        {
            $expected = $expression;
        }

        /** @var float $phpResult */
        $phpResult = 0.0;
        eval('$phpResult = ' . $expected . ';');

        try {
            $result = $calculator->execute($expression);
        } catch (Exception $e) {
            $this->fail(\sprintf('Exception: %s (%s:%d), expression was: %s', $e::class, $e->getFile(), $e->getLine(), $expression));
        }
        $this->assertEquals($phpResult, $result, "Expression was: {$expression}");
    }

    /**
     * Expressions data provider
     *
     * Most tests can go in here.  The idea is that each expression will be evaluated by MathExecutor and by PHP with eval.
     * The results should be the same.  If they are not, then the test fails.  No need to add extra test unless you are doing
     * something more complex and not a simple mathmatical expression.
     *
     * @return array<array<string>>
     */
    public function bcMathExpressions()
    {
        return [
          ['-5'],
          ['-5+10'],
          ['4-5'],
          ['4 -5'],
          ['(4*2)-5'],
          ['(4*2) - 5'],
          ['4*-5'],
          ['4 * -5'],
          ['+5'],
          ['+(3+2)'],
          ['+(+3+2)'],
          ['+(-3+2)'],
          ['-5'],
          ['-(-5)'],
          ['-(+5)'],
          ['+(-5)'],
          ['+(+5)'],
          ['-(3+2)'],
          ['-(-3+-2)'],

          ['abs(1.5)'],
          ['acos(0.15)'],
          ['acosh(1.5)'],
          ['asin(0.15)'],
          ['atan(0.15)'],
          ['atan2(1.5, 3.5)'],
          ['atanh(0.15)'],
          ['bindec("10101")'],
          ['ceil(1.5)'],
          ['cos(1.5)'],
          ['cosh(1.5)'],
          ['decbin("15")'],
          ['dechex("15")'],
          ['decoct("15")'],
          ['deg2rad(1.5)'],
          ['exp(1.5)'],
          ['expm1(1.5)'],
          ['floor(1.5)'],
          ['fmod(1.5, 3.5)'],
          ['hexdec("abcdef")'],
          ['hypot(1.5, 3.5)'],
          ['intdiv(10, 2)'],
          ['log(1.5)'],
          ['log10(1.5)'],
          ['log1p(1.5)'],
          ['max(1.5, 3.5)'],
          ['min(1.5, 3.5)'],
          ['octdec("15")'],
          ['pi()'],
          ['pow(1.5, 3.5)'],
          ['rad2deg(1.5)'],
          ['round(1.5)'],
          ['sin(1.5)'],
          ['sin(12)'],
          ['+sin(12)'],
          ['-sin(12)', '0.53'],
          ['sinh(1.5)'],
          ['sqrt(1.5)'],
          ['tan(1.5)'],
          ['tanh(1.5)'],

          ['0.1 + 0.2', '0.30'],
          ['0.1 + 0.2 - 0.3', '0.00'],
          ['1 + 2'],

          ['0.1 - 0.2'],
          ['1 - 2'],

          ['0.1 * 2'],
          ['1 * 2'],

          ['0.1 / 0.2'],
          ['1 / 2'],

          ['2 * 2 + 3 * 3'],
          ['2 * 2 / 3 * 3', '3.99'],
          ['2 / 2 / 3 / 3', '0.11'],
          ['2 / 2 * 3 / 3'],
          ['2 / 2 * 3 * 3'],

          ['1 + 0.6 - 3 * 2 / 50'],

          ['(5 + 3) * -1'],

          ['-2- 2*2'],
          ['2- 2*2'],
          ['2-(2*2)'],
          ['(2- 2)*2'],
          ['2 + 2*2'],
          ['2+ 2*2'],
          ['2+2*2'],
          ['(2+2)*2'],
          ['(2 + 2)*-2'],
          ['(2+-2)*2'],

          ['1 + 2 * 3 / (min(1, 5) + 2 + 1)'],
          ['1 + 2 * 3 / (min(1, 5) - 2 + 5)'],
          ['1 + 2 * 3 / (min(1, 5) * 2 + 1)'],
          ['1 + 2 * 3 / (min(1, 5) / 2 + 1)'],
          ['1 + 2 * 3 / (min(1, 5) / 2 * 1)'],
          ['1 + 2 * 3 / (min(1, 5) / 2 / 1)'],
          ['1 + 2 * 3 / (3 + min(1, 5) + 2 + 1)', '1.85'],
          ['1 + 2 * 3 / (3 - min(1, 5) - 2 + 1)'],
          ['1 + 2 * 3 / (3 * min(1, 5) * 2 + 1)', '1.85'],
          ['1 + 2 * 3 / (3 / min(1, 5) / 2 + 1)'],

          ['(1 + 2) * 3 / (3 / min(1, 5) / 2 + 1)'],

          ['sin(10) * cos(50) / min(10, 20/2)', '-0.05'],
          ['sin(10) * cos(50) / min(10, (20/2))', '-0.05'],
          ['sin(10) * cos(50) / min(10, (max(10,20)/2))', '-0.05'],

          ['1 + "2" / 3', '1.66'],
          ["1.5 + '2.5' / 4", '2.12'],
          ['1.5 + "2.5" * ".5"'],

          ['-1 + -2'],
          ['-1+-2'],
          ['-1- -2'],
          ['-1/-2'],
          ['-1*-2'],

          ['(1+2+3+4-5)*7/100'],
          ['(-1+2+3+4- 5)*7/100'],
          ['(1+2+3+4- 5)*7/100'],
          ['( 1 + 2 + 3 + 4 - 5 ) * 7 / 100'],

          ['1 && 0'],
          ['1 && 0 && 1'],
          ['1 || 0'],
          ['1 && 0 || 1'],

          ['5 == 3'],
          ['5 == 5'],
          ['5 != 3'],
          ['5 != 5'],
          ['5 > 3'],
          ['3 > 5'],
          ['3 >= 5'],
          ['3 >= 3'],
          ['3 < 5'],
          ['5 < 3'],
          ['3 <= 5'],
          ['5 <= 5'],
          ['10 < 9 || 4 > (2+1)'],
          ['10 < 9 || 4 > (-2+1)'],
          ['10 < 9 || 4 > (2+1) && 5 == 5 || 4 != 6 || 3 >= 4 || 3 <= 7'],

          ['1 + 5 == 3 + 1'],
          ['1 + 5 == 5 + 1'],
          ['1 + 5 != 3 + 1'],
          ['1 + 5 != 5 + 1'],
          ['1 + 5 > 3 + 1'],
          ['1 + 3 > 5 + 1'],
          ['1 + 3 >= 5 + 1'],
          ['1 + 3 >= 3 + 1'],
          ['1 + 3 < 5 + 1'],
          ['1 + 5 < 3 + 1'],
          ['1 + 3 <= 5 + 1'],
          ['1 + 5 <= 5 + 1'],

          ['(-4)'],
          ['(-4 + 5)'],
          ['(3 * 1)'],
          ['(-3 * -1)'],
          ['1 + (-3 * -1)'],
          ['1 + ( -3 * 1)'],
          ['1 + (3 *-1)'],
          ['1 - 0'],
          ['1-0'],

          ['-(1.5)'],
          ['-log(4)', '-1.38'],
          ['0-acosh(1.5)', '-0.96'],
          ['-acosh(1.5)', '-0.96'],
          ['-(-4)'],
          ['-(-4 + 5)'],
          ['-(3 * 1)'],
          ['-(-3 * -1)'],
          ['-1 + (-3 * -1)'],
          ['-1 + ( -3 * 1)'],
          ['-1 + (3 *-1)'],
          ['-1 - 0'],
          ['-1-0'],
          ['-(4*2)-5'],
          ['-(4*-2)-5'],
          ['-(-4*2) - 5'],
          ['-4*-5'],
          ['max(1,2,4.9,3)'],
          ['min(1,2,4.9,3)'],
          ['max([1,2,4.9,3])'],
          ['min([1,2,4.9,3])'],

          ['4 % 4'],
          ['7 % 4'],
          ['99 % 4'],
          ['123 % 7'],
          ['!(1||0)'],
          ['!(1&&0)'],
        ];
    }

    /**
     * @dataProvider incorrectExpressions
     */
    public function testIncorrectExpressionException(string $expression) : void
    {
        $calculator = new MathExecutor();
        $calculator->setVars(['a' => 12, 'b' => 24]);
        $this->expectException(IncorrectExpressionException::class);
        $calculator->execute($expression);
    }

    /**
     * Incorrect Expressions data provider
     *
     * These expressions should not pass validation
     *
     * @return array<array<string>>
     */
    public function incorrectExpressions()
    {
        return [
          ['1 * + '],
          [' 2 3'],
          ['2 3 '],
          [' 2 4 3 '],
          ['$a $b'],
          ['$a [3, 4, 5]'],
          ['$a (3 + 4)'],
          ['$a "string"'],
          ['5 "string"'],
          ['"string" $a'],
          ['$a round(12.345)'],
          ['round(12.345) $a'],
          ['4 round(12.345)'],
          ['round(12.345) 4'],
        ];
    }

    public function testUnknownFunctionException() : void
    {
        $calculator = new MathExecutor();
        $this->expectException(UnknownFunctionException::class);
        $calculator->execute('1 * fred("wilma") + 3');
    }

    public function testZeroDivision() : void
    {
        $calculator = new MathExecutor();
        $calculator->setDivisionByZeroIsZero();
        $this->assertEquals(0, $calculator->execute('10 / 0'));
    }

    public function testUnaryOperators() : void
    {
        $calculator = new MathExecutor();
        $this->assertEquals(5, $calculator->execute('+5'));
        $this->assertEquals(5, $calculator->execute('+(3+2)'));
        $this->assertEquals(-5, $calculator->execute('-5'));
        $this->assertEquals(5, $calculator->execute('-(-5)'));
        $this->assertEquals(-5, $calculator->execute('+(-5)'));
        $this->assertEquals(-5, $calculator->execute('-(3+2)'));
    }

    public function testZeroDivisionException() : void
    {
        $calculator = new MathExecutor();
        $this->expectException(DivisionByZeroException::class);
        $calculator->execute('10 / 0');
        $calculator->setVar('one', 1)->setVar('zero', 0);
        $this->assertEquals(0.0, $calculator->execute('$one / $zero'));
    }

    public function testVariableIncorrectExpressionException() : void
    {
        $calculator = new MathExecutor();
        $calculator->setVar('four', 4);
        $this->assertEquals(4, $calculator->execute('$four'));
        $this->expectException(IncorrectExpressionException::class);
        $this->assertEquals(0.0, $calculator->execute('$'));
        $this->assertEquals(0.0, $calculator->execute('$ + $four'));
    }

    public function testExponentiation() : void
    {
        $calculator = new MathExecutor();
        $this->assertEquals(100, $calculator->execute('10 ^ 2'));
    }

    public function testStringEscape() : void
    {
        $calculator = new MathExecutor();
        $this->assertEquals("test\string", $calculator->execute('"test\string"'));
        $this->assertEquals("\\test\string\\", $calculator->execute('"\test\string\\\\"'));
        $this->assertEquals('\test\string\\', $calculator->execute('"\test\string\\\\"'));
        $this->assertEquals('test\\\\string', $calculator->execute('"test\\\\\\\\string"'));
        $this->assertEquals('test"string', $calculator->execute('"test\"string"'));
        $this->assertEquals('test""string', $calculator->execute('"test\"\"string"'));
        $this->assertEquals('"teststring', $calculator->execute('"\"teststring"'));
        $this->assertEquals('teststring"', $calculator->execute('"teststring\""'));
        $this->assertEquals("test'string", $calculator->execute("'test\'string'"));
        $this->assertEquals("test''string", $calculator->execute("'test\'\'string'"));
        $this->assertEquals("'teststring", $calculator->execute("'\'teststring'"));
        $this->assertEquals("teststring'", $calculator->execute("'teststring\''"));

        $calculator->addFunction('concat', static fn($arg1, $arg2) => $arg1 . $arg2);
        $this->assertEquals('test"ing', $calculator->execute('concat("test\"","ing")'));
        $this->assertEquals("test'ing", $calculator->execute("concat('test\'','ing')"));
    }

    public function testArrays() : void
    {
        $calculator = new MathExecutor();
        $this->assertEquals([1, 5, 2], $calculator->execute('array(1, 5, 2)'));
        $this->assertEquals([1, 5, 2], $calculator->execute('[1, 5, 2]'));
        $this->assertEquals(\max([1, 5, 2]), $calculator->execute('max([1, 5, 2])'));
        $this->assertEquals(\max([1, 5, 2]), $calculator->execute('max(array(1, 5, 2))'));
        $calculator->addFunction('arr_with_max_elements', static function($arg1, ...$args) {
            $args = \is_array($arg1) ? $arg1 : [$arg1, ...$args];
            \usort($args, static fn($arr1, $arr2) => (\is_countable($arr2) ? \count($arr2) : 0) <=> \count($arr1));

            return $args[0];
        });
        $this->assertEquals([3, 3, 3], $calculator->execute('arr_with_max_elements([[1],array(2,2),[3,3,3]])'));
    }

    public function testFunctionParameterOrder() : void
    {
        $calculator = new MathExecutor();

        $calculator->addFunction('concat', static fn($arg1, $arg2) => $arg1 . $arg2);
        $this->assertEquals('testing', $calculator->execute('concat("test","ing")'));
        $this->assertEquals('testing', $calculator->execute("concat('test','ing')"));
    }

    public function testFunction() : void
    {
        $calculator = new MathExecutor();
        $calculator->addFunction('round', static fn($arg) => \round($arg));
        $this->assertEquals(\round(100 / 30), $calculator->execute('round(100/30)'));
    }

    public function testFunctionUnlimitedParameters() : void
    {
        $calculator = new MathExecutor();
        $calculator->addFunction('give_me_an_array', static fn() => [5, 3, 7, 9, 8]);
        $this->assertEquals(6.4, $calculator->execute('avg(give_me_an_array())'));
        $this->assertEquals(10, $calculator->execute('avg(12,8,15,5)'));
        $this->assertEquals(3, $calculator->execute('min(give_me_an_array())'));
        $this->assertEquals(1, $calculator->execute('min(1,2,3)'));
        $this->assertEquals(9, $calculator->execute('max(give_me_an_array())'));
        $this->assertEquals(3, $calculator->execute('max(1,2,3)'));
        $this->assertEquals(7, $calculator->execute('median(give_me_an_array())'));
        $this->assertEquals(4, $calculator->execute('median(1,3,5,7)'));
        $calculator->setVar('monthly_salaries', [100, 200, 300]);
        $this->assertEquals([100, 200, 300], $calculator->execute('$monthly_salaries'));
        $this->assertEquals(200, $calculator->execute('avg($monthly_salaries)'));
        $this->assertEquals(\min([100, 200, 300]), $calculator->execute('min($monthly_salaries)'));
        $this->assertEquals(\max([100, 200, 300]), $calculator->execute('max($monthly_salaries)'));
        $this->assertEquals(200, $calculator->execute('median($monthly_salaries)'));
    }

    public function testFunctionOptionalParameters() : void
    {
        $calculator = new MathExecutor();
        $calculator->addFunction('round', static fn($num, $precision = 0) => \round($num, $precision));
        $this->assertEquals(\round(11.176), $calculator->execute('round(11.176)'));
        $this->assertEquals(\round(11.176, 2), $calculator->execute('round(11.176,2)'));
    }

    public function testFunctionIncorrectNumberOfParameters() : void
    {
        $calculator = new MathExecutor();
        $this->expectException(IncorrectNumberOfFunctionParametersException::class);
        $calculator->addFunction('myfunc', static fn($arg1, $arg2) => $arg1 + $arg2);
        $calculator->execute('myfunc(1)');
    }

    public function testFunctionIncorrectNumberOfParametersTooMany() : void
    {
        $calculator = new MathExecutor();
        $this->expectException(IncorrectNumberOfFunctionParametersException::class);
        $calculator->addFunction('myfunc', static fn($arg1, $arg2) => $arg1 + $arg2);
        $calculator->execute('myfunc(1,2,3)');
    }

    public function testFunctionIf() : void
    {
        $calculator = new MathExecutor();
        $this->assertEquals(
          30,
          $calculator->execute(
            'if(100 > 99, 30, 0)'
          ),
          'Expression failed: if(100 > 99, 30, 0)'
        );
        $this->assertEquals(
          0,
          $calculator->execute(
            'if(100 < 99, 30, 0)'
          ),
          'Expression failed: if(100 < 99, 30, 0)'
        );
        $this->assertEquals(
          30,
          $calculator->execute(
            'if(98 < 99 && sin(1) < 1, 30, 0)'
          ),
          'Expression failed: if(98 < 99 && sin(1) < 1, 30, 0)'
        );
        $this->assertEquals(
          40,
          $calculator->execute(
            'if(98 < 99 && sin(1) < 1, max(30, 40), 0)'
          ),
          'Expression failed: if(98 < 99 && sin(1) < 1, max(30, 40), 0)'
        );
        $this->assertEquals(
          40,
          $calculator->execute(
            'if(98 < 99 && sin(1) < 1, if(10 > 5, max(30, 40), 1), 0)'
          ),
          'Expression failed: if(98 < 99 && sin(1) < 1, if(10 > 5, max(30, 40), 1), 0)'
        );
        $this->assertEquals(
          20,
          $calculator->execute(
            'if(98 < 99 && sin(1) > 1, if(10 > 5, max(30, 40), 1), if(4 <= 4, 20, 21))'
          ),
          'Expression failed: if(98 < 99 && sin(1) > 1, if(10 > 5, max(30, 40), 1), if(4 <= 4, 20, 21))'
        );
        $this->assertEquals(
          \cos(2),
          $calculator->execute(
            'if(98 < 99 && sin(1) >= 1, max(30, 40), cos(2))'
          ),
          'Expression failed: if(98 < 99 && sin(1) >= 1, max(30, 40), cos(2))'
        );
        $this->assertEquals(
          \cos(2),
          $calculator->execute(
            'if(cos(2), cos(2), 0)'
          ),
          'Expression failed: if(cos(2), cos(2), 0)'
        );
        $trx_amount = 100000;
        $calculator->setVar('trx_amount', $trx_amount);
        $this->assertEquals($trx_amount, $calculator->execute('$trx_amount'));
        $this->assertEquals(
          $trx_amount * 0.03,
          $calculator->execute(
            'if($trx_amount < 40000, $trx_amount * 0.06, $trx_amount * 0.03)'
          ),
          'Expression failed: if($trx_amount < 40000, $trx_amount * 0.06, $trx_amount * 0.03)'
        );
        $this->assertEquals(
          $trx_amount * 0.03,
          $calculator->execute(
            'if($trx_amount < 40000, $trx_amount * 0.06, if($trx_amount < 60000, $trx_amount * 0.05, $trx_amount * 0.03))'
          ),
          'Expression failed: if($trx_amount < 40000, $trx_amount * 0.06, if($trx_amount < 60000, $trx_amount * 0.05, $trx_amount * 0.03))'
        );
        $trx_amount = 39000;
        $calculator->setVar('trx_amount', $trx_amount);
        $this->assertEquals(
          $trx_amount * 0.06,
          $calculator->execute(
            'if($trx_amount < 40000, $trx_amount * 0.06, if($trx_amount < 60000, $trx_amount * 0.05, $trx_amount * 0.03))'
          ),
          'Expression failed: if($trx_amount < 40000, $trx_amount * 0.06, if($trx_amount < 60000, $trx_amount * 0.05, $trx_amount * 0.03))'
        );
        $trx_amount = 59000;
        $calculator->setVar('trx_amount', $trx_amount);
        $this->assertEquals(
          $trx_amount * 0.05,
          $calculator->execute(
            'if($trx_amount < 40000, $trx_amount * 0.06, if($trx_amount < 60000, $trx_amount * 0.05, $trx_amount * 0.03))'
          ),
          'Expression failed: if($trx_amount < 40000, $trx_amount * 0.06, if($trx_amount < 60000, $trx_amount * 0.05, $trx_amount * 0.03))'
        );
        $this->expectException(IncorrectNumberOfFunctionParametersException::class);
        $this->assertEquals(
          0.0,
          $calculator->execute(
            'if($trx_amount < 40000, $trx_amount * 0.06)'
          ),
          'Expression failed: if($trx_amount < 40000, $trx_amount * 0.06)'
        );
    }

    public function testVariables() : void
    {
        $calculator = new MathExecutor();
        $this->assertEquals(3.14159265359, $calculator->execute('$pi'));
        $this->assertEquals(3.14159265359, $calculator->execute('pi'));
        $this->assertEquals(2.71828182846, $calculator->execute('$e'));
        $this->assertEquals(2.71828182846, $calculator->execute('e'));
        $calculator->setVars([
          'trx_amount' => 100000.01,
          'ten' => 10,
          'nine' => 9,
          'eight' => 8,
          'seven' => 7,
          'six' => 6,
          'five' => 5,
          'four' => 4,
          'three' => 3,
          'two' => 2,
          'one' => 1,
          'zero' => 0,
        ]);
        $this->assertEquals(100000.01, $calculator->execute('$trx_amount'));
        $this->assertEquals(10 - 9, $calculator->execute('$ten - $nine'));
        $this->assertEquals(9 - 10, $calculator->execute('$nine - $ten'));
        $this->assertEquals(10 + 9, $calculator->execute('$ten + $nine'));
        $this->assertEquals(10 * 9, $calculator->execute('$ten * $nine'));
        $this->assertEquals(10 / 9, $calculator->execute('$ten / $nine'));
        $this->assertEquals(10 / (9 / 5), $calculator->execute('$ten / ($nine / $five)'));

        // test variables without leading $
        $this->assertEquals(100000.01, $calculator->execute('trx_amount'));
        $this->assertEquals(10 - 9, $calculator->execute('ten - nine'));
        $this->assertEquals(9 - 10, $calculator->execute('nine - ten'));
        $this->assertEquals(10 + 9, $calculator->execute('ten + nine'));
        $this->assertEquals(10 * 9, $calculator->execute('ten * nine'));
        $this->assertEquals(10 / 9, $calculator->execute('ten / nine'));
        $this->assertEquals(10 / (9 / 5), $calculator->execute('ten / (nine / five)'));
    }

    public function testEvaluateFunctionParameters() : void
    {
        $calculator = new MathExecutor();
        $calculator->addFunction(
          'round',
          static fn($value, $decimals) => \round($value, $decimals)
        );
        $expression = 'round(100 * 1.111111, 2)';
        $phpResult = 0;
        eval('$phpResult = ' . $expression . ';');
        $this->assertEquals($phpResult, $calculator->execute($expression));
        $expression = 'round((100*0.04)+(((100*1.02)+0.5)*1.28),2)';
        eval('$phpResult = ' . $expression . ';');
        $this->assertEquals($phpResult, $calculator->execute($expression));
    }

    public function testFunctionsWithQuotes() : void
    {
        $calculator = new MathExecutor();
        $calculator->addFunction('concat', static fn($first, $second) => $first . $second);
        $this->assertEquals('testing', $calculator->execute('concat("test", "ing")'));
        $this->assertEquals('testing', $calculator->execute("concat('test', 'ing')"));
    }

    public function testQuotes() : void
    {
        $calculator = new MathExecutor();
        $testString = 'some, long. arg; with: different-separators!';
        $calculator->addFunction(
          'test',
          function($arg) use ($testString) {
                $this->assertEquals($testString, $arg);

                return 0;
            }
        );
        $calculator->execute('test("' . $testString . '")'); // single quotes
        $calculator->execute("test('" . $testString . "')"); // double quotes
    }

    public function testBeginWithBracketAndMinus() : void
    {
        $calculator = new MathExecutor();
        $this->assertEquals(-4, $calculator->execute('(-4)'));
        $this->assertEquals(1, $calculator->execute('(-4 + 5)'));
    }

    public function testStringComparison() : void
    {
        $calculator = new MathExecutor();
        $this->assertEquals(true, $calculator->execute('"a" == \'a\''));
        $this->assertEquals(true, $calculator->execute('"hello world" == "hello world"'));
        $this->assertEquals(false, $calculator->execute('"hello world" == "hola mundo"'));
        $this->assertEquals(true, $calculator->execute('"hello world" != "hola mundo"'));
        $this->assertEquals(true, $calculator->execute('"a" < "b"'));
        $this->assertEquals(false, $calculator->execute('"a" > "b"'));
        $this->assertEquals(true, $calculator->execute('"a" <= "b"'));
        $this->assertEquals(false, $calculator->execute('"a" >= "b"'));
        $this->assertEquals(true, $calculator->execute('"A" != "a"'));
    }

    public function testVarStringComparison() : void
    {
        $calculator = new MathExecutor();
        $calculator->setVar('var', 97);
        $this->assertEquals(false, $calculator->execute('97 == "a"'));
        $this->assertEquals(false, $calculator->execute('$var == "a"'));
        $calculator->setVar('var', 'a');
        $this->assertEquals(true, $calculator->execute('$var == "a"'));
    }

    public function testOnVarNotFound() : void
    {
        $calculator = new MathExecutor();
        $calculator->setVarNotFoundHandler(
          static function($varName) {
                if ('undefined' == $varName) {
                    return 3;
                }
            }
        );
        $this->assertEquals(15, $calculator->execute('5 * undefined'));
        $this->assertEquals(3, $calculator->getVar('undefined'));
        $this->assertNull($calculator->getVar('Lucy'));
    }

    public function testGetVarException() : void
    {
        $calculator = new MathExecutor();
        $this->expectException(UnknownVariableException::class);
        $this->assertNull($calculator->getVar('Lucy'));
    }

    public function testMinusZero() : void
    {
        $calculator = new MathExecutor();
        $this->assertEquals(1, $calculator->execute('1 - 0'));
        $this->assertEquals(1, $calculator->execute('1-0'));
    }

    public function testScientificNotation() : void
    {
        $calculator = new MathExecutor();
        $this->assertEquals(1.5e9, $calculator->execute('1.5e9'));
        $this->assertEquals(1.5e-9, $calculator->execute('1.5e-9'));
        $this->assertEquals(1.5e+9, $calculator->execute('1.5e+9'));
    }

    public function testNullReturnType() : void
    {
        $calculator = new MathExecutor();
        $calculator->setVar('nullValue', null);
        $this->assertEquals(null, $calculator->execute('nullValue'));
    }

    public function testGetFunctionsReturnsArray() : void
    {
        $calculator = new MathExecutor();
        $this->assertIsArray($calculator->getFunctions());
    }

    public function testGetFunctionsReturnsFunctions() : void
    {
        $calculator = new MathExecutor();
        $this->assertGreaterThan(40, \count($calculator->getFunctions()));
    }

    public function testGetVarsReturnsArray() : void
    {
        $calculator = new MathExecutor();
        $this->assertIsArray($calculator->getVars());
    }

    public function testGetVarsReturnsCount() : void
    {
        $calculator = new MathExecutor();
        $this->assertGreaterThan(1, \count($calculator->getVars()));
    }

    public function testUndefinedVarThrowsExecption() : void
    {
        $calculator = new MathExecutor();
        $this->assertGreaterThan(1, \count($calculator->getVars()));
        $this->expectException(UnknownVariableException::class);
        $calculator->execute('5 * undefined');
    }

    public function testSetVarsAcceptsAllScalars() : void
    {
        $calculator = new MathExecutor();
        $calculator->setVar('boolTrue', true);
        $calculator->setVar('boolFalse', false);
        $calculator->setVar('int', 1);
        $calculator->setVar('null', null);
        $calculator->setVar('float', 1.1);
        $calculator->setVar('string', 'string');
        $this->assertCount(8, $calculator->getVars());
        $this->assertEquals(true, $calculator->getVar('boolTrue'));
        $this->assertEquals(false, $calculator->getVar('boolFalse'));
        $this->assertEquals(1, $calculator->getVar('int'));
        $this->assertEquals(null, $calculator->getVar('null'));
        $this->assertEquals(1.1, $calculator->getVar('float'));
        $this->assertEquals('string', $calculator->getVar('string'));

        $this->expectException(MathExecutorException::class);
        $calculator->setVar('validVar', new \DateTime());
    }

    public function testSetVarsDoesNotAcceptObject() : void
    {
        $calculator = new MathExecutor();
        $this->expectException(MathExecutorException::class);
        $calculator->setVar('object', $this);
    }

    public function testSetVarsDoesNotAcceptResource() : void
    {
        $calculator = new MathExecutor();
        $this->expectException(MathExecutorException::class);
        $calculator->setVar('resource', \tmpfile());
    }

    public function testSetCustomVarValidator() : void
    {
        $calculator = new MathExecutor();
        $calculator->setVarValidationHandler(static function(string $name, $variable) : void {
            // allow all scalars and null
            if (\is_scalar($variable) || null === $variable) {
                return;
            }
            // Allow variables of type DateTime, but not others
            if (! $variable instanceof \DateTime) {
                throw new MathExecutorException('Invalid variable type');
            }
        });

        $calculator->setVar('validFloat', 0.0);
        $calculator->setVar('validInt', 0);
        $calculator->setVar('validTrue', true);
        $calculator->setVar('validFalse', false);
        $calculator->setVar('validString', 'string');
        $calculator->setVar('validNull', null);
        $calculator->setVar('validDateTime', new \DateTime());

        $this->expectException(MathExecutorException::class);
        $calculator->setVar('validVar', $this);
    }

    public function testSetCustomVarNameValidator() : void
    {
        $calculator = new MathExecutor();
        $calculator->setVarValidationHandler(static function(string $name, $variable) : void {
            // don't allow variable names with the word invalid in them
            if (\str_contains($name, 'invalid')) {
                throw new MathExecutorException('Invalid variable name');
            }
        });

        $calculator->setVar('validFloat', 0.0);
        $calculator->setVar('validInt', 0);
        $calculator->setVar('validTrue', true);
        $calculator->setVar('validFalse', false);
        $calculator->setVar('validString', 'string');
        $calculator->setVar('validNull', null);
        $calculator->setVar('validDateTime', new \DateTime());

        $this->expectException(MathExecutorException::class);
        $calculator->setVar('invalidVar', 12);
    }

    public function testVarExists() : void
    {
        $calculator = new MathExecutor();
        $varName = 'Eythel';
        $calculator->setVar($varName, 1);
        $this->assertTrue($calculator->varExists($varName));
        $this->assertFalse($calculator->varExists('Lucy'));
    }

    /**
     * @dataProvider providerExpressionValues
     */
    public function testCalculatingValues(string $expression, mixed $value) : void
    {
        $calculator = new MathExecutor();

        try {
            $result = $calculator->execute($expression);
        } catch (Exception $e) {
            $this->fail(\sprintf('Exception: %s (%s:%d), expression was: %s', $e::class, $e->getFile(), $e->getLine(), $expression));
        }
        $this->assertEquals($value, $result, "{$expression} did not evaluate to {$value}");
    }

    /**
     * Expressions data provider
     *
     * Most tests can go in here.  The idea is that each expression will be evaluated by MathExecutor and by PHP directly.
     * The results should be the same.  If they are not, then the test fails.  No need to add extra test unless you are doing
     * something more complex and not a simple mathmatical expression.
     *
     * @return array<array<mixed>>
     */
    public function providerExpressionValues()
    {
        return [
          ['arccos(0.5)', \acos(0.5)],
          ['arccosec(4)', \asin(1 / 4)],
          ['arccot(3)', M_PI / 2 - \atan(3)],
          ['arccotan(4)', M_PI / 2 - \atan(4)],
          ['arccsc(4)', \asin(1 / 4)],
          ['arcctg(3)', M_PI / 2 - \atan(3)],
          ['arcsec(4)', \acos(1 / 4)],
          ['arcsin(0.5)', \asin(0.5)],
          ['arctan(0.5)', \atan(0.5)],
          ['arctan(4)', \atan(4)],
          ['arctg(0.5)', \atan(0.5)],
          ['cosec(12)', 1 / \sin(12)],
          ['cosec(4)', 1 / \sin(4)],
          ['cosh(12)', \cosh(12)],
          ['cot(12)', \cos(12) / \sin(12)],
          ['cotan(12)', \cos(12) / \sin(12)],
          ['cotan(4)', \cos(4) / \sin(4)],
          ['cotg(3)', \cos(3) / \sin(3)],
          ['csc(4)', 1 / \sin(4)],
          ['ctg(4)', \cos(4) / \sin(4)],
          ['ctn(4)', \cos(4) / \sin(4)],
          ['decbin(10)', \decbin(10)],
          ['lg(2)', \log10(2)],
          ['ln(2)', \log(2)],
          ['sec(4)', 1 / \cos(4)],
          ['tg(4)', \tan(4)],
        ];
    }

    public function testCache() : void
    {
        $calculator = new MathExecutor();
        $this->assertEquals(256, $calculator->execute('2 ^ 8')); // second arg $cache is true by default

        $this->assertIsArray($calculator->getCache());
        $this->assertCount(1, $calculator->getCache());

        $this->assertEquals(512, $calculator->execute('2 ^ 9', true));
        $this->assertCount(2, $calculator->getCache());

        $this->assertEquals(1024, $calculator->execute('2 ^ 10', false));
        $this->assertCount(2, $calculator->getCache());

        $calculator->clearCache();
        $this->assertIsArray($calculator->getCache());
        $this->assertCount(0, $calculator->getCache());

        $this->assertEquals(2048, $calculator->execute('2 ^ 11', false));
        $this->assertCount(0, $calculator->getCache());
    }

    public function testUnsupportedOperands() : void
    {
        if (\version_compare(PHP_VERSION, '8') >= 0) {
            $calculator = new MathExecutor();

            $calculator->setVar('stringVar', 'string');
            $calculator->setVar('intVar', 1);

            $this->expectException(\TypeError::class);
            $calculator->execute('stringVar + intVar');
        } else {
            $this->expectNotToPerformAssertions();
        }
    }
}
