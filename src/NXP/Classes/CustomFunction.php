<?php

namespace NXP\Classes;

use NXP\Exception\IncorrectFunctionParameterException;
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

    private ReflectionFunction $reflectionFunction;

    /**
     * CustomFunction constructor.
     *
     * @throws ReflectionException
     */
    public function __construct(string $name, callable $function)
    {
        $this->name = $name;
        $this->function = $function;
        $this->reflectionFunction = new ReflectionFunction($function);

    }

    /**
     * @param array<Token> $stack
     *
     * @throws IncorrectNumberOfFunctionParametersException|IncorrectFunctionParameterException
     */
    public function execute(array &$stack, int $paramCountInStack) : Token
    {
        if ($paramCountInStack < $this->reflectionFunction->getNumberOfRequiredParameters()) {
            throw new IncorrectNumberOfFunctionParametersException($this->name);
        }
        $args = [];

        if ($paramCountInStack > 0) {
            $reflectionParameters = $this->reflectionFunction->getParameters();

            for ($i = 0; $i < $paramCountInStack; $i++) {
                $value = \array_pop($stack)->value;
                $valueType = \gettype($value);
                $reflectionParameter = $reflectionParameters[\min(\count($reflectionParameters) - 1, $i)];
                //TODO to support type check for union types (php >= 8.0) and intersection types (php >= 8.1), we should increase min php level in composer.json
                // For now, only support basic types. @see testFunctionParameterTypes
                if ($reflectionParameter->hasType() && $reflectionParameter->getType()->getName() !== $valueType){
                    throw new IncorrectFunctionParameterException();
                }

                \array_unshift($args, $value);
            }
        }

        $result = \call_user_func_array($this->function, $args);

        return new Token(Token::Literal, $result);
    }
}
