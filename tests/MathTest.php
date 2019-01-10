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

class MathTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerExpressions
     */
    public function testCalculating($expression)
    {
        $calculator = new MathExecutor();

        /** @var float $phpResult */
        eval('$phpResult = ' . $expression . ';');
        $this->assertEquals($calculator->execute($expression), $phpResult, "Expression was: ${expression}");
    }

    /**
     * Expressions data provider
     */
    public function providerExpressions()
    {
        return [
            ['0.1 + 0.2'],
            ['1 + 2'],

            ['0.1 - 0.2'],
            ['1 - 2'],

            ['0.1 * 2'],
            ['1 * 2'],

            ['0.1 / 0.2'],
            ['1 / 2'],

            ['2 * 2 + 3 * 3'],

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
        $this->assertEquals($calculator->execute('10 / 0'), 0);
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
        $this->assertEquals($calculator->execute('10 ^ 2'), 100);
    }

    public function testFunction()
    {
        $calculator = new MathExecutor();

        $calculator->addFunction('round', function ($arg) {
            return round($arg);
        }, 1);
        /** @var float $phpResult */
        eval('$phpResult = round(100/30);');
        $this->assertEquals($calculator->execute('round(100/30)'), $phpResult);
    }

    public function testQuotes()
    {
        $calculator = new MathExecutor();
        $testString = "some, long. arg; with: different-separators!";
        $calculator->addFunction('test', function ($arg) use ($testString) {
            $this->assertEquals($arg, $testString);
            return 0;
        }, 1);
        $calculator->execute('test("some, long. arg; with: different-separators!")'); // single quotes
        $calculator->execute("test('some, long. arg; with: different-separators!')"); // double quotes
    }
}