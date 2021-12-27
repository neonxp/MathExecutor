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
    public function testCalculating($expression)
    {
        $calculator = new MathExecutor();

        /** @var float $phpResult */
        eval('$phpResult = ' . $expression . ';');
        try {
            $result = $calculator->execute($expression);
        } catch (Exception $e) {
            $this->fail(sprintf("Exception: %s (%s:%d), expression was: %s", get_class($e), $e->getFile(), $e->getLine(), $expression));
        }
        $this->assertEquals($phpResult, $result, "Expression was: ${expression}");
    }

    /**
     * Expressions data provider
     *
     * Most tests can go in here.  The idea is that each expression will be evaluated by MathExecutor and by PHP with eval.
     * The results should be the same.  If they are not, then the test fails.  No need to add extra test unless you are doing
     * something more complex and not a simple mathmatical expression.
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
        ];
    }

    public function testUnknownFunctionException()
    {
        $calculator = new MathExecutor();
        $this->expectException(UnknownFunctionException::class);
        $calculator->execute('1 * fred("wilma") + 3');
    }

    public function testIncorrectExpressionException()
    {
        $calculator = new MathExecutor();
        $this->expectException(IncorrectExpressionException::class);
        $calculator->execute('1 * + ');
    }

    public function testZeroDivision()
    {
        $calculator = new MathExecutor();
        $calculator->setDivisionByZeroIsZero();
        $this->assertEquals(0, $calculator->execute('10 / 0'));
    }

    public function testUnaryOperators()
    {
        $calculator = new MathExecutor();
        $this->assertEquals(5, $calculator->execute('+5'));
        $this->assertEquals(5, $calculator->execute('+(3+2)'));
        $this->assertEquals(-5, $calculator->execute('-5'));
        $this->assertEquals(5, $calculator->execute('-(-5)'));
        $this->assertEquals(-5, $calculator->execute('+(-5)'));
        $this->assertEquals(-5, $calculator->execute('-(3+2)'));
    }

    public function testZeroDivisionException()
    {
        $calculator = new MathExecutor();
        $this->expectException(DivisionByZeroException::class);
        $calculator->execute('10 / 0');
        $calculator->setVar('one', 1)->setVar('zero', 0);
        $this->assertEquals(0.0, $calculator->execute('$one / $zero'));
    }

    public function testVariableIncorrectExpressionException()
    {
        $calculator = new MathExecutor();
        $calculator->setVar('four', 4);
        $this->assertEquals(4, $calculator->execute('$four'));
        $this->expectException(IncorrectExpressionException::class);
        $this->assertEquals(0.0, $calculator->execute('$'));
        $this->assertEquals(0.0, $calculator->execute('$ + $four'));
    }

    public function testExponentiation()
    {
        $calculator = new MathExecutor();
        $this->assertEquals(100, $calculator->execute('10 ^ 2'));
    }

    public function testFunctionParameterOrder()
    {
        $calculator = new MathExecutor();

        $calculator->addFunction('concat', function ($arg1, $arg2) {
            return $arg1 . $arg2;
        });
        $this->assertEquals('testing', $calculator->execute('concat("test","ing")'));
        $this->assertEquals('testing', $calculator->execute("concat('test','ing')"));
    }

    public function testFunction()
    {
        $calculator = new MathExecutor();
        $calculator->addFunction('round', function ($arg) {
            return round($arg);
        });
        $this->assertEquals(round(100 / 30), $calculator->execute('round(100/30)'));
    }

    public function testFunctionIf()
    {
        $calculator = new MathExecutor();
        $this->assertEquals(30, $calculator->execute(
            'if(100 > 99, 30, 0)'
        ));
        $this->assertEquals(0, $calculator->execute(
            'if(100 < 99, 30, 0)'
        ));
        $this->assertEquals(30, $calculator->execute(
            'if(98 < 99 && sin(1) < 1, 30, 0)'
        ));
        $this->assertEquals(40, $calculator->execute(
            'if(98 < 99 && sin(1) < 1, max(30, 40), 0)'
        ));
        $this->assertEquals(40, $calculator->execute(
            'if(98 < 99 && sin(1) < 1, if(10 > 5, max(30, 40), 1), 0)'
        ));
        $this->assertEquals(20, $calculator->execute(
            'if(98 < 99 && sin(1) > 1, if(10 > 5, max(30, 40), 1), if(4 <= 4, 20, 21))'
        ));
        $this->assertEquals(cos(2), $calculator->execute(
            'if(98 < 99 && sin(1) >= 1, max(30, 40), cos(2))'
        ));
        $this->assertEquals(cos(2), $calculator->execute(
            'if(cos(2), cos(2), 0)'
        ));
        $trx_amount = 100000;
        $calculator->setVar('trx_amount', $trx_amount);
        $this->assertEquals($trx_amount, $calculator->execute('$trx_amount'));
        $this->assertEquals($trx_amount * 0.03, $calculator->execute(
            'if($trx_amount < 40000, $trx_amount * 0.06, $trx_amount * 0.03)'
        ));
        $this->assertEquals($trx_amount * 0.03, $calculator->execute(
            'if($trx_amount < 40000, $trx_amount * 0.06, if($trx_amount < 60000, $trx_amount * 0.05, $trx_amount * 0.03))'
        ));
        $trx_amount = 39000;
        $calculator->setVar('trx_amount', $trx_amount);
        $this->assertEquals($trx_amount * 0.06, $calculator->execute(
            'if($trx_amount < 40000, $trx_amount * 0.06, if($trx_amount < 60000, $trx_amount * 0.05, $trx_amount * 0.03))'
        ));
        $trx_amount = 59000;
        $calculator->setVar('trx_amount', $trx_amount);
        $this->assertEquals($trx_amount * 0.05, $calculator->execute(
            'if($trx_amount < 40000, $trx_amount * 0.06, if($trx_amount < 60000, $trx_amount * 0.05, $trx_amount * 0.03))'
        ));
        $this->expectException(IncorrectNumberOfFunctionParametersException::class);
        $this->assertEquals(0.0, $calculator->execute(
            'if($trx_amount < 40000, $trx_amount * 0.06)'
        ));
    }

    public function testVariables()
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

    public function testEvaluateFunctionParameters()
    {
        $calculator = new MathExecutor();
        $calculator->addFunction(
            'round',
            function ($value, $decimals) {
                return round($value, $decimals);
            }
        );
        $expression = 'round(100 * 1.111111, 2)';
        $phpResult = 0;
        eval('$phpResult = ' . $expression . ';');
        $this->assertEquals($phpResult, $calculator->execute($expression));
        $expression = 'round((100*0.04)+(((100*1.02)+0.5)*1.28),2)';
        eval('$phpResult = ' . $expression . ';');
        $this->assertEquals($phpResult, $calculator->execute($expression));
    }

    public function testFunctionsWithQuotes()
    {
        $calculator = new MathExecutor();
        $calculator->addFunction('concat', function ($first, $second) {
            return $first . $second;
        });
        $this->assertEquals('testing', $calculator->execute('concat("test", "ing")'));
        $this->assertEquals('testing', $calculator->execute("concat('test', 'ing')"));
    }

    public function testQuotes()
    {
        $calculator = new MathExecutor();
        $testString = "some, long. arg; with: different-separators!";
        $calculator->addFunction(
            'test',
            function ($arg) use ($testString) {
                $this->assertEquals($testString, $arg);
                return 0;
            }
        );
        $calculator->execute('test("' . $testString . '")'); // single quotes
        $calculator->execute("test('" . $testString . "')"); // double quotes
    }

    public function testBeginWithBracketAndMinus()
    {
        $calculator = new MathExecutor();
        $this->assertEquals(-4, $calculator->execute('(-4)'));
        $this->assertEquals(1, $calculator->execute('(-4 + 5)'));
    }

    public function testStringComparison()
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

    public function testVarStringComparison()
    {
        $calculator = new MathExecutor();
        $calculator->setVar('var', 97);
        $this->assertEquals(false, $calculator->execute('97 == "a"'));
        $this->assertEquals(false, $calculator->execute('$var == "a"'));
        $calculator->setVar('var', 'a');
        $this->assertEquals(true, $calculator->execute('$var == "a"'));
    }

    public function testOnVarNotFound()
    {
        $calculator = new MathExecutor();
        $calculator->setVarNotFoundHandler(
            function ($varName) {
                if ($varName == 'undefined') {
                    return 3;
                }
                return null;
            }
        );
        $this->assertEquals(15, $calculator->execute('5 * undefined'));
    }

    public function testMinusZero()
    {
        $calculator = new MathExecutor();
        $this->assertEquals(1, $calculator->execute('1 - 0'));
        $this->assertEquals(1, $calculator->execute('1-0'));
    }

    public function testScientificNotation()
    {
        $calculator = new MathExecutor();
        $this->assertEquals(1.5e9, $calculator->execute('1.5e9'));
        $this->assertEquals(1.5e-9, $calculator->execute('1.5e-9'));
        $this->assertEquals(1.5e+9, $calculator->execute('1.5e+9'));
    }

    public function testGetFunctionsReturnsArray()
    {
        $calculator = new MathExecutor();
        $this->assertIsArray($calculator->getFunctions());
    }

    public function testGetFunctionsReturnsFunctions()
    {
        $calculator = new MathExecutor();
        $this->assertGreaterThan(40, count($calculator->getFunctions()));
    }

    public function testGetVarsReturnsArray()
    {
        $calculator = new MathExecutor();
        $this->assertIsArray($calculator->getVars());
    }

    public function testGetVarsReturnsCount()
    {
        $calculator = new MathExecutor();
        $this->assertGreaterThan(1, count($calculator->getVars()));
    }

    public function testUndefinedVarThrowsExecption()
    {
        $calculator = new MathExecutor();
        $this->assertGreaterThan(1, count($calculator->getVars()));
        $this->expectException(UnknownVariableException::class);
        $calculator->execute('5 * undefined');
    }

    public function testSetVarsAcceptsAllScalars()
    {
        $calculator = new MathExecutor();
        $calculator->setVar('boolTrue', true);
        $calculator->setVar('boolFalse', false);
        $calculator->setVar('int', 1);
        $calculator->setVar('null', null);
        $calculator->setVar('float', 1.1);
        $calculator->setVar('string', 'string');
        $this->assertEquals(8, count($calculator->getVars()));
        $this->assertEquals(true, $calculator->getVar('boolTrue'));
        $this->assertEquals(false, $calculator->getVar('boolFalse'));
        $this->assertEquals(1, $calculator->getVar('int'));
        $this->assertEquals(null, $calculator->getVar('null'));
        $this->assertEquals(1.1, $calculator->getVar('float'));
        $this->assertEquals('string', $calculator->getVar('string'));
    }

    public function testSetVarsDoesNotAcceptObject()
    {
        $calculator = new MathExecutor();
        $this->expectException(MathExecutorException::class);
        $calculator->setVar('object', $this);
    }

    public function testSetVarsDoesNotAcceptResource()
    {
        $calculator = new MathExecutor();
        $this->expectException(MathExecutorException::class);
        $calculator->setVar('resource', tmpfile());
    }

    /**
     * @dataProvider providerExpressionValues
     */
    public function testCalculatingValues($expression, $value)
    {
        $calculator = new MathExecutor();

        try {
            $result = $calculator->execute($expression);
        } catch (Exception $e) {
            $this->fail(sprintf("Exception: %s (%s:%d), expression was: %s", get_class($e), $e->getFile(), $e->getLine(), $expression));
        }
        $this->assertEquals($value, $result, "${expression} did not evaluate to {$value}");
    }

    /**
     * Expressions data provider
     *
     * Most tests can go in here.  The idea is that each expression will be evaluated by MathExecutor and by PHP directly.
     * The results should be the same.  If they are not, then the test fails.  No need to add extra test unless you are doing
     * something more complex and not a simple mathmatical expression.
     */
    public function providerExpressionValues()
    {
        return [
            ['arccos(0.5)', 1.0471975511966],
            ['arccos(0.5)', acos(0.5)],
            ['arccosec(4)', 0.2526802551421],
            ['arccosec(4)', asin(1/4)],
            ['arccot(3)', M_PI/2 - atan(3)],
            ['arccotan(4)', 0.2449786631269],
            ['arccotan(4)', M_PI/2 - atan(4)],
            ['arccsc(4)', 0.2526802551421],
            ['arccsc(4)', asin(1/4)],
            ['arcctg(3)', M_PI/2 - atan(3)],
            ['arcsec(4)', 1.3181160716528],
            ['arcsec(4)', acos(1/4)],
            ['arcsin(0.5)', 0.5235987755983],
            ['arcsin(0.5)', asin(0.5)],
            ['arctan(0.5)', atan(0.5)],
            ['arctan(4)', 1.3258176636680],
            ['arctg(0.5)', atan(0.5)],
            ['cosec(12)', 1 / sin(12)],
            ['cosec(4)', -1.3213487088109],
            ['cosh(12)', cosh(12)],
            ['cot(12)', cos(12) / sin(12)],
            ['cotan(12)', cos(12) / sin(12)],
            ['cotan(4)', 0.8636911544506],
            ['cotg(3)', cos(3) / sin(3)],
            ['csc(4)', 1 / sin(4)],
            ['ctg(4)', cos(4) / sin(4)],
            ['ctn(4)', cos(4) / sin(4)],
            ['decbin(10)', decbin(10)],
            ['lg(2)', 0.3010299956639],
            ['lg(2)', log10(2)],
            ['ln(2)', 0.6931471805599],
            ['ln(2)', log(2)],
            ['sec(4)', -1.5298856564664],
            ['tg(4)', 1.1578212823496],
        ];
    }

    public function testCache()
    {
        $calculator = new MathExecutor();
        $this->assertEquals(256, $calculator->execute('2 ^ 8')); // second arg $cache is true by default

        $this->assertIsArray($calculator->getCache());
        $this->assertEquals(1, count($calculator->getCache()));

        $this->assertEquals(512, $calculator->execute('2 ^ 9', true));
        $this->assertEquals(2, count($calculator->getCache()));

        $this->assertEquals(1024, $calculator->execute('2 ^ 10', false));
        $this->assertEquals(2, count($calculator->getCache()));

        $calculator->clearCache();
        $this->assertIsArray($calculator->getCache());
        $this->assertEquals(0, count($calculator->getCache()));

        $this->assertEquals(2048, $calculator->execute('2 ^ 11', false));
        $this->assertEquals(0, count($calculator->getCache()));


    }

}