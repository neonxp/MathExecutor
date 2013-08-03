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

class Token
{
    const NOTHING       = 'NOTHING';
    const STRING        = 'STRING';
    const NUMBER        = 'NUMBER';
    const OPERATOR      = 'OPERATOR';
    const LEFT_BRACKET  = 'LEFT_BRACKET';
    const RIGHT_BRACKET = 'RIGHT_BRACKET';
    const FUNC          = 'FUNC';

    /**
     * @var string
     */
    protected $value;

    /**
     * @var string
     */
    protected $type;

    public function __construct($type, $value)
    {
        $this->type = $type;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

}
