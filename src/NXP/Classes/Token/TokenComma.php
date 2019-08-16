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
 * @author Alexander Kiryukhin <a.kiryukhin@mail.ru>
 */
class TokenComma extends AbstractOperator
{
    /**
     * @return string
     */
    public static function getRegex()
    {
        return '\,';
    }

    /**
     * Comma operator is lowest priority
     *
     * @return int
     */
    public function getPriority()
    {
        return 0;
    }

    /**
     * @return string
     */
    public function getAssociation()
    {
        return self::LEFT_ASSOC;
    }

    /**
     * @param  array       $stack
     * @return TokenNumber
     */
    public function execute(&$stack)
    {
        // Comma operators don't do anything, stack has already executed
    }

}
