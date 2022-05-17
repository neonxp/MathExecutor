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

    private int $requiredParamCount;

    /**
     * CustomFunction constructor.
     *
     * @throws ReflectionException
     */
    public function __construct(string $name, callable $function)
    {
        $this->name = $name;
        $this->function = $function;
        $this->requiredParamCount = (new ReflectionFunction($function))->getNumberOfRequiredParameters();

    }

    /**
     * @param array<Token> $stack
     *
     * @throws IncorrectNumberOfFunctionParametersException
     */
    public function execute(array &$stack, int $paramCountInStack) : Token
    {
        if ($paramCountInStack < $this->requiredParamCount) {
            throw new IncorrectNumberOfFunctionParametersException($this->name);
        }
        $args = [];

        if ($paramCountInStack > 0) {
            for ($i = 0; $i < $paramCountInStack; $i++) {
                \array_unshift($args, \array_pop($stack)->value);
            }
        }

        $result = \call_user_func_array($this->function, $args);

        return new Token(Token::Literal, $result);
    }
}
