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

use NXP\Exception\IncorrectExpressionException;
use NXP\Exception\DivisionByZeroException;

/**
* @author Alexander Kiryukhin <a.kiryukhin@mail.ru>
*/
class TokenDivision extends AbstractOperator
{
    /**
     * @return string
     */
    public static function getRegex()
    {
        return '\/';
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return 180;
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
     *
     * @return $this
     *
     * @throws \NXP\Exception\IncorrectExpressionException
     * @throws \NXP\Exception\DivisionByZeroException
     */
    public function execute(&$stack)
    {
        $op2 = array_pop($stack);
        $op1 = array_pop($stack);

        if ($op1 === null || $op2 === null) {
            throw new IncorrectExpressionException("Division requires two operators");
        }

        if ($this->getDivisionByZeroException() && $op2->getValue() == 0) {
            throw new DivisionByZeroException();
        }

        $result = $op2->getValue() != 0 ? $op1->getValue() / $op2->getValue() : 0;

        return new TokenNumber($result);
    }
}
