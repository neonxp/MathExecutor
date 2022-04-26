<?php

namespace NXP\Classes;

use NXP\Exception\IncorrectNumberOfFunctionParametersException;
use ReflectionException;
use ReflectionFunction;

class CustomFunction
{
    public string $name = '';

    /**
     * @var callable $function
     */
    public $function;

    public int $places = 0;

    /**
     * CustomFunction constructor.
     * @param int $places
     * @throws ReflectionException
     * @throws IncorrectNumberOfFunctionParametersException
     */
    public function __construct(string $name, callable $function, ?int $places = null)
    {
        $this->name = $name;
        $this->function = $function;

        if (null === $places) {
            $reflection = new ReflectionFunction($function);
            $this->places = $reflection->getNumberOfParameters();
        } else {
            $this->places = $places;
        }
    }

    /**
     * @param array<Token> $stack
     *
     * @throws IncorrectNumberOfFunctionParametersException
     */
    public function execute(array &$stack) : Token
    {
        if (\count($stack) < $this->places) {
            throw new IncorrectNumberOfFunctionParametersException($this->name);
        }
        $args = [];

        for ($i = 0; $i < $this->places; $i++) {
            \array_unshift($args, \array_pop($stack)->value);
        }

        $result = \call_user_func_array($this->function, $args);

        return new Token(Token::Literal, $result);
    }
}
