<?php

namespace NXP\Classes\Token;

use NXP\Exception\IncorrectExpressionException;

class TokenLessThan extends AbstractOperator
{
    /**
     * @return string
     */
    public static function getRegex()
    {
        return '<';
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return 150;
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
     */
    public function execute(&$stack)
    {
        $op2 = array_pop($stack);
        $op1 = array_pop($stack);

        if ($op1 === null || $op2 === null) {
            throw new IncorrectExpressionException("< requires two operators");
        }

        $result = $op1->getValue() < $op2->getValue();

        return new TokenNumber($result);
    }
}
