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
abstract class AbstractOperator implements InterfaceToken, InterfaceOperator
{
    const RIGHT_ASSOC   = 'RIGHT';
    const LEFT_ASSOC    = 'LEFT';
}
