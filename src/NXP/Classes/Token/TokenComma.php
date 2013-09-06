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
class TokenComma implements InterfaceToken
{
    /**
     * @return string
     */
    public static function getRegex()
    {
        return '\,';
    }
}
