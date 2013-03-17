<?php
/**
 * Author: Alexander "NeonXP" Kiryukhin
 * Date: 17.03.13
 * Time: 3:23
 */

namespace NXP\Classes;


class Token {

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

    function __construct($type, $value)
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