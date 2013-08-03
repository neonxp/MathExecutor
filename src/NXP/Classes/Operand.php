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

class Operand
{
    const LEFT_ASSOCIATED = 'LEFT_ASSOCIATED';
    const RIGHT_ASSOCIATED = 'RIGHT_ASSOCIATED';
    const ASSOCIATED = 'ASSOCIATED';

    const UNARY  = 'UNARY';
    const BINARY = 'BINARY';

    /**
     * @var string
     */
    private $symbol;

    /**
     * @var int
     */
    private $priority;

    /**
     * @var string
     */
    private $association;

    /**
     * @var string
     */
    private $type;

    /**
     * @var callable
     */
    private $callback;

    /**
     * @param $symbol
     * @param $priority
     * @param $association
     * @param $type
     * @param $callback
     */
    public function __construct($symbol, $priority, $association, $type, $callback)
    {
        $this->association = $association;
        $this->symbol = $symbol;
        $this->type = $type;
        $this->priority = $priority;
        $this->callback = $callback;
    }

    public function getAssociation()
    {
        return $this->association;
    }

    public function getSymbol()
    {
        return $this->symbol;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getCallback()
    {
        return $this->callback;
    }

    public function getPriority()
    {
        return $this->priority;
    }

}
