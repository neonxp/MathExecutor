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
interface InterfaceOperator
{
    /**
     * @return int
     */
    public function getPriority();

    /**
     * @return string
     */
    public function getAssociation();

    /**
     * @param  array       $stack
     * @return TokenNumber
     */
    public function execute(&$stack);
}
