<?php
/**
 * This file is part of the MathExecutor package
 *
 * (c) Alexander Kiryukhin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace NXP\Classes;

use NXP\Classes\Token\InterfaceOperator;
use NXP\Classes\Token\TokenFunction;
use NXP\Classes\Token\TokenNumber;
use NXP\Exception\IncorrectExpressionException;

/**
 * @author Alexander Kiryukhin <alexander@symdev.org>
 */
class Calculator
{
    /**
     * @param  array                                       $tokens Tokens in reverse polish notation
     * @return number
     * @throws \NXP\Exception\IncorrectExpressionException
     */
    public function calculate($tokens)
    {
        $stack = array();
        foreach ($tokens as $token) {
            if ($token instanceof TokenNumber) {
                array_push($stack, $token);
            }
            if ($token instanceof InterfaceOperator || $token instanceof TokenFunction) {
                array_push($stack, $token->execute($stack));
            }
        }
        $result = array_pop($stack);
        if (!empty($stack)) {
            throw new IncorrectExpressionException();
        }

        return $result->getValue();
    }
}
