<?php

namespace NXP\Classes;

class Token
{
    public const Literal = "literal";
    public const Variable = "variable";
    public const Operator = "operator";
    public const LeftParenthesis = "LP";
    public const RightParenthesis = "RP";
    public const Function = "function";
    public const ParamSeparator = "separator";
    public const String = "string";
    public const Space = "space";

    /** @var self::* */
    public $type = self::Literal;

    /** @var float|string */
    public $value;

    /** @var string */
    public $name;

    /**
     * Token constructor.
     * @param self::* $type
     * @param float|string $value
     */
    public function __construct(string $type, $value, string $name = null)
    {
        $this->type = $type;
        $this->value = $value;
        $this->name = $name;
    }
}
