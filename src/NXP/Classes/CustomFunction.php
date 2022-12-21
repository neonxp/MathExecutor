<?php

namespace NXP\Classes;

use NXP\Exception\IncorrectNumberOfFunctionParametersException;
use ReflectionException;
use ReflectionFunction;

class CustomFunction
{
    /**
     * @var callable $function
     */
    public $function;

    private bool $isVariadic;

    private int $totalParamCount;

    private int $requiredParamCount;

    /**
     * CustomFunction constructor.
     *
     * @throws ReflectionException
     */
    public function __construct(public string $name, callable $function)
    {
        $this->function = $function;
        $reflection = (new ReflectionFunction($function));
        $this->isVariadic = $reflection->isVariadic();
        $this->totalParamCount = $reflection->getNumberOfParameters();
        $this->requiredParamCount = $reflection->getNumberOfRequiredParameters();

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

        if ($paramCountInStack > $this->totalParamCount && ! $this->isVariadic) {
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
