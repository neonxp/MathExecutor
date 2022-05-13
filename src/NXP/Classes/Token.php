<?php

namespace NXP\Classes;

class Token
{
    public const Literal = 'literal';

    public const Variable = 'variable';

    public const Operator = 'operator';

    public const LeftParenthesis = 'LP';

    public const RightParenthesis = 'RP';

    public const Function = 'function';

    public const ParamSeparator = 'separator';

    public const String = 'string';

    public const Space = 'space';

    public string $type = self::Literal;

    public $value;

    public ?string $name;

    public ?int $paramCount = null;//to store function parameter count in stack

    /**
     * Token constructor.
     *
     */
    public function __construct(string $type, $value, ?string $name = null)
    {
        $this->type = $type;
        $this->value = $value;
        $this->name = $name;
    }
}
