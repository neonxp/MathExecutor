<?php


namespace NXP\Classes;


use NXP\Exception\IncorrectExpressionException;
use ReflectionException;
use ReflectionFunction;

class CustomFunction
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var callable $function
     */
    public $function;

    /**
     * @var int
     */
    public $places;

    /**
     * CustomFunction constructor.
     * @param string $name
     * @param callable $function
     * @param int $places
     * @throws ReflectionException
     */
    public function __construct(string $name, callable $function, ?int $places = null)
    {
        $this->name = $name;
        $this->function = $function;
        if ($places === null) {
            $reflection = new ReflectionFunction($function);
            $this->places = $reflection->getNumberOfParameters();
        } else {
            $this->places = $places;
        }
    }

    public function execute(array &$stack) : Token
    {
        if (count($stack) < $this->places) {
            throw new IncorrectExpressionException();
        }
        $args = [];
        for ($i = 0; $i < $this->places; $i++) {
            array_unshift($args, array_pop($stack)->value);
        }

        $result = call_user_func_array($this->function, $args);

        return new Token(Token::Literal, $result);
    }


}