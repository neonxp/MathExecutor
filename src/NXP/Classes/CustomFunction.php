<?php


namespace NXP\Classes;

use NXP\Exception\IncorrectNumberOfFunctionParametersException;
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
    public $placesOptional;

    /**
     * CustomFunction constructor.
     * @param string $name
     * @param callable $function
     * @param int $places
     * @throws ReflectionException
     * @throws IncorrectNumberOfFunctionParametersException
     */
    public function __construct(string $name, callable $function, ?int $places = null) {
        $this->name = $name;
        $this->function = $function;
        if ($places === null) {
            $reflection = new ReflectionFunction($function);
            $this->places = $reflection->getNumberOfRequiredParameters();
            $this->placesOptional = $reflection->getNumberOfParameters();
        } else {
            $this->places = $places;
            $this->placesOptional = $places;
        }
    }

    public function execute(array &$stack): Token {
        if (count($stack) < $this->places) {
            throw new IncorrectNumberOfFunctionParametersException($this->name);
        }
        $args = [];

        for ($i = 0; $i < $this->placesOptional; $i++) {
            array_unshift($args, array_pop($stack)->value);
        }
        $args = array_values(array_filter($args));
        $result = call_user_func_array($this->function, $args);

        return new Token(Token::Literal, $result);
    }
}
