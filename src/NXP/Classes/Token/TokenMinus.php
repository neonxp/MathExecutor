<?php
/**
* This file is part of the MathExecutor package
*
* (c) Alexander Kiryukhin
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code
*/

namespace NXP\Classes\Token;

/**
* @author Alexander Kiryukhin <alexander@symdev.org>
*/
class TokenMinus extends AbstractOperator
{
    /**
     * @return string
     */
    public static function getRegex()
    {
        return '\-';
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return 1;
    }

    /**
     * @return string
     */
    public function getAssociation()
    {
        return self::LEFT_ASSOC;
    }

    /**
     * @param InterfaceToken[] $stack
     * @return $this
     */
    public function execute(&$stack)
    {
        $op2 = array_pop($stack);
        $op1 = array_pop($stack);
        $result = $op1->getValue() - $op2->getValue();

        return new TokenNumber($result);
    }
}
