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

use NXP\MathExecutor;
use NXP\Exception\DivisionByZeroException;
use NXP\Exception\IncorrectBracketsException;
use NXP\Exception\IncorrectExpressionException;
use NXP\Exception\MathExecutorException;
use NXP\Exception\UnknownFunctionException;
use NXP\Exception\UnknownOperatorException;
use NXP\Exception\UnknownTokenException;
use NXP\Exception\UnknownVariableException;

class MathTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider providerExpressions
     */
    public function testCalculating($expression)
    {
        $calculator = new MathExecutor();

        /** @var float $phpResult */
        eval('$phpResult = ' . $expression . ';');
        $this->assertEquals($phpResult, $calculator->execute($expression), "Expression was: ${expression}");
    }

    /**
     * Expressions data provider
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

            [cos(2)],

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
        $this->assertEquals(0, $calculator->execute('10 / 0'));
    }

    public function testZeroDivisionException()
    {
        $calculator = new MathExecutor();
        $calculator->setDivisionByZeroException();
        $this->expectException(DivisionByZeroException::class);
        $calculator->execute('10 / 0');
    }

    public function testExponentiation()
    {
        $calculator = new MathExecutor();
        $this->assertEquals(100, $calculator->execute('10 ^ 2'));
    }

    public function testFunctionParameterOrder()
    {
        $calculator = new MathExecutor();

        $calculator->addFunction('concat', function ($arg1, $arg2) {return $arg1.$arg2;});
        $this->assertEquals('testing', $calculator->execute('concat("test","ing")'));
        $this->assertEquals('testing', $calculator->execute("concat('test','ing')"));
    }

    public function testFunction()
    {
        $calculator = new MathExecutor();
        $calculator->addFunction('round', function ($arg) {return round($arg);});
        $this->assertEquals(round(100/30), $calculator->execute('round(100/30)'));
    }

    public function testFunctionIf()
    {
        $calculator = new MathExecutor();
        $this->assertEquals(30, $calculator->execute(
            'if(100 > 99, 30, 0)'));
        $this->assertEquals(0, $calculator->execute(
            'if(100 < 99, 30, 0)'));
        $this->assertEquals(30, $calculator->execute(
            'if(98 < 99 && sin(1) < 1, 30, 0)'));
        $this->assertEquals(40, $calculator->execute(
            'if(98 < 99 && sin(1) < 1, max(30, 40), 0)'));
        $this->assertEquals(40, $calculator->execute(
            'if(98 < 99 && sin(1) < 1, if(10 > 5, max(30, 40), 1), 0)'));
        $this->assertEquals(20, $calculator->execute(
            'if(98 < 99 && sin(1) > 1, if(10 > 5, max(30, 40), 1), if(4 <= 4, 20, 21))'));
        $this->assertEquals(cos(2), $calculator->execute(
            'if(98 < 99 && sin(1) >= 1, max(30, 40), cos(2))'));
        $this->assertEquals(cos(2), $calculator->execute(
            'if(cos(2), cos(2), 0)'));
    }

    public function testEvaluateFunctionParameters()
    {
        $calculator = new MathExecutor();
        $calculator->addFunction('round', function ($value, $decimals)
          {
            return round($value, $decimals);
          }
        );
        $expression = 'round(100 * 1.111111, 2)';
        eval('$phpResult = ' . $expression . ';');
        $this->assertEquals($phpResult, $calculator->execute($expression));
        $expression = 'round((100*0.04)+(((100*1.02)+0.5)*1.28),2)';
        eval('$phpResult = ' . $expression . ';');
        $this->assertEquals($phpResult, $calculator->execute($expression));
    }

    public function testFunctionsWithQuotes()
    {
        $calculator = new MathExecutor();
        $calculator->addFunction('concat', function($first, $second){return $first.$second;});
        $this->assertEquals('testing', $calculator->execute('concat("test", "ing")'));
        $this->assertEquals('testing', $calculator->execute("concat('test', 'ing')"));
    }

    public function testQuotes()
    {
        $calculator = new MathExecutor();
        $testString = "some, long. arg; with: different-separators!";
        $calculator->addFunction('test', function ($arg) use ($testString)
            {
            $this->assertEquals($testString, $arg);
            return 0;
            }
        );
        $calculator->execute('test("' . $testString . '")'); // single quotes
        $calculator->execute("test('" . $testString . "')"); // double quotes
    }
}
