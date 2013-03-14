<?php
/**
 * Author: Alexander "NeonXP" Kiryukhin
 * Date: 14.03.13
 * Time: 3:41
 */
namespace NXP\Tests;

use \NXP\MathExecutor;

class MathTest extends \PHPUnit_Framework_TestCase {
    public function setup()
    {
        require '../MathExecutor.php';
    }
    public function testCalculating()
    {
        $calculator = new MathExecutor();
        for ($i = 1; $i <= 10; $i++) {
            $expression = $this->generateExpression();
            print "Test #$i. Expression: '$expression'\t";

            eval('$result1 = ' . $expression . ';');
            print "PHP result: $result1 \t";
            $result2 = $calculator->execute($expression);
            print "NXP Math Executor result: $result2\n";
            $this->assertEquals($result1, $result2);
        }
    }

    private function generateExpression()
    {
        $operators = [ '+', '-', '*', '/' ];
        $number = true;
        $expression = '';
        $brackets = 0;
        for ($i = 1; $i < rand(1,10)*2; $i++) {
            if ($number) {
                $expression .= rand(1,100)*0.5;
            } else {
                $expression .= $operators[rand(0,3)];
            }
            $number = !$number;
            $rand = rand(1,5);
            if (($rand == 1) && ($number)) {
                $expression .= '(';
                $brackets++;
            } elseif (($rand == 2) && (!$number) && ($brackets > 0)) {
                $expression .= ')';
                $brackets--;
            }
        }
        if ($number) {
            $expression .= rand(1,100)*0.5;
        }
        $expression .=  str_repeat(')', $brackets);

        return $expression;
    }
}