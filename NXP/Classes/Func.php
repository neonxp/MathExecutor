<?php
/**
 * Author: Alexander "NeonXP" Kiryukhin
 * Date: 17.03.13
 * Time: 4:30
 */

namespace NXP\Classes;


class Func {
    /**
     * @var string
     */
    private $name;

    /**
     * @var callable
     */
    private $callback;

    /**
     * @param $name
     * @param $callback
     */
    function __construct($name, $callback)
    {
        $this->name = $name;
        $this->callback = $callback;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getCallback()
    {
        return $this->callback;
    }
}