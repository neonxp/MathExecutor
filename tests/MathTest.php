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

use \NXP\MathExecutor;

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
        $this->assertEquals($calculator->execute($expression), $phpResult);
    }

    /**
     * Expressions data provider
     */
    public function providerExpressions()
    {
        return array(
            array('0.1 + 0.2'),
            array('1 + 2'),

            array('0.1 - 0.2'),
            array('1 - 2'),

            array('0.1 * 2'),
            array('1 * 2'),

            array('0.1 / 0.2'),
            array('1 / 2'),

            array('2 * 2 + 3 * 3'),

            array('1 + 0.6 - 3 * 2 / 50'),

            array('(5 + 3) * -1'),

            array('2+2*2'),
            array('(2+2)*2'),
            array('(2+2)*-2'),
            array('(2+-2)*2'),

            array('sin(10) * cos(50) / min(10, 20/2)'),

            array('100500 * 3.5E5'),
            array('100500 * 3.5E-5')
        );
    }
}