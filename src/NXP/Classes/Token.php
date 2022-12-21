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

    public ?int $paramCount = null;//to store function parameter count in stack

    /**
     * Token constructor.
     *
     */
    public function __construct(public string $type, public mixed $value, public ?string $name = null)
    {
    }
}
