<?php

namespace NXP\Classes;

use NXP\Exception\IncorrectExpressionException;
use ReflectionFunction;

class Operator
{
    /**
     * @var string
     */
    public $operator;

    /**
     * @var bool
     */
    public $isRightAssoc;

    /**
     * @var int
     */
    public $priority;

    /**
     * @var callable(\SplStack)
     */
    public $function;

    /**
     * @var int
     */
    public $places;

    /**
     * Operator constructor.
     * @param string $operator
     * @param bool $isRightAssoc
     * @param int $priority
     * @param callable $function
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
    public function execute(array &$stack): Token
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
