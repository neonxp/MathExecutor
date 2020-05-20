<?php


namespace NXP\Classes;

class Token
{
    const Literal = "literal";
    const Variable = "variable";
    const Operator = "operator";
    const LeftParenthesis = "LP";
    const RightParenthesis = "RP";
    const Function = "function";
    const ParamSeparator = "separator";
    const String = "string";
    const Space = "space";

    public $type = self::Literal;

    /**
     * @var float|string
     */
    public $value;

    /**
     * Token constructor.
     * @param string $type
     * @param float|string $value
     */
    public function __construct(string $type, $value)
    {
        $this->type = $type;
        $this->value = $value;
    }
}
