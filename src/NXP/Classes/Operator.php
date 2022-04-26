<?php

namespace NXP\Classes;

use NXP\Exception\IncorrectExpressionException;
use ReflectionFunction;

class Operator
{
    public string $operator = '';

    public bool $isRightAssoc = false;

    public int $priority = 0;

    /**
     * @var callable(\SplStack)
     */
    public $function;

    public int $places = 0;

    /**
     * Operator constructor.
     */
    public function __construct(string $operator, bool $isRightAssoc, int $priority, callable $function)
    {
        $this->operator = $operator;
        $this->isRightAssoc = $isRightAssoc;
        $this->priority = $priority;
        $this->function = $function;
        $reflection = new ReflectionFunction($function);
        $this->places = $reflection->getNumberOfParameters();
    }

    /**
     * @param array<Token> $stack
     *
     * @throws IncorrectExpressionException
     */
    public function execute(array &$stack) : Token
    {
        if (\count($stack) < $this->places) {
            throw new IncorrectExpressionException();
        }
        $args = [];

        for ($i = 0; $i < $this->places; $i++) {
            \array_unshift($args, \array_pop($stack)->value);
        }

        $result = \call_user_func_array($this->function, $args);

        return new Token(Token::Literal, $result);
    }
}
