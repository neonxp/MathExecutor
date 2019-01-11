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
abstract class AbstractOperator implements InterfaceToken, InterfaceOperator
{
    const RIGHT_ASSOC   = 'RIGHT';
    const LEFT_ASSOC    = 'LEFT';

    /**
     * Divide by zero reporting
     *
     * @var bool
     */
    private $divideByZeroReporting = false;

    /**
     * Set division by zero exception reporting
     *
     * @param bool $exception default true
     *
     * @return $this
     */
    public function setDivisionByZeroException($exception = true)
    {
        $this->divideByZeroReporting = $exception;
        return $this;
    }

    /**
     * Get division by zero exception status
     *
     * @return bool
     */
    public function getDivisionByZeroException()
    {
        return $this->divideByZeroReporting;
    }
}
