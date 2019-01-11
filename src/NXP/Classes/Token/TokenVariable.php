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
class TokenVariable extends AbstractContainerToken
{
    /**
     * @return string
     */
    public static function getRegex()
    {
        return '\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*';
    }
}
