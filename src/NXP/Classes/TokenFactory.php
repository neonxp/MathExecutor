<?php
/**
 * This file is part of the MathExecutor package
 *
 * (c) Alexander Kiryukhin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace NXP\Classes;

use NXP\Classes\Token\InterfaceToken;
use NXP\Classes\Token\TokenComma;
use NXP\Classes\Token\TokenFunction;
use NXP\Classes\Token\TokenLeftBracket;
use NXP\Classes\Token\TokenNumber;
use NXP\Classes\Token\TokenRightBracket;
use NXP\Classes\Token\TokenStringSingleQuoted;
use NXP\Classes\Token\TokenVariable;
use NXP\Classes\Token\TokenStringDoubleQuoted;
use NXP\Exception\UnknownFunctionException;
use NXP\Exception\UnknownOperatorException;
use NXP\Exception\UnknownTokenException;

/**
 * @author Alexander Kiryukhin <a.kiryukhin@mail.ru>
 */
class TokenFactory
{
    /**
     * Available operators
     *
     * @var array
     */
    protected $operators = [];

    /**
     * Divide by zero reporting
     *
     * @var bool
     */
    protected $divideByZeroReporting = false;

    /**
     * Available functions
     *
     * @var array
     */
    protected $functions = [];

    /**
     * Add function
     * @param string $name
     * @param callable $function
     * @param int $places
     * @return TokenFactory
     * @throws \ReflectionException
     */
    public function addFunction($name, callable $function, $places = null)
    {
        if ($places === null) {
            $reflector = new \ReflectionFunction($function);
            $places = $reflector->getNumberOfParameters();
        }
        $this->functions[$name] = [$places, $function];

        return $this;
    }

    /**
     * get functions
     *
     * @return array containing callback and places indexed by
     *         function name
     */
    public function getFunctions()
    {
        return $this->functions;
    }

    /**
     * Add operator
     * @param  string $operatorClass
     * @return TokenFactory
     * @throws UnknownOperatorException
     * @throws \ReflectionException
     */
    public function addOperator($operatorClass)
    {
        $class = new \ReflectionClass($operatorClass);

        if (!in_array('NXP\Classes\Token\InterfaceToken', $class->getInterfaceNames())) {
            throw new UnknownOperatorException($operatorClass);
        }

        $this->operators[$operatorClass::getRegex()] = $operatorClass;

        return $this;
    }

    /**
     * Get registered operators
     *
     * @return array of operator class names
     */
    public function getOperators()
    {
        return $this->operators;
    }

    /**
     * Set division by zero exception reporting
     *
     * @param bool $exception default true
     *
     * @return TokenFactory
     */
    public function setDivisionByZeroException($exception = true)
    {
        $this->divideByZeroReporting = $exception;

        return $this;
    }

    /**
     * Get division by zero exception status
     *
     * @return bool
     */
    public function getDivisionByZeroException()
    {
        return $this->divideByZeroReporting;
    }

    /**
     * @return string
     */
    public function getTokenParserRegex()
    {
        $operatorsRegex = '';
        foreach ($this->operators as $operator) {
            $operatorsRegex .= '|(' . $operator::getRegex() . ')';
        }
        $s = sprintf(
            '/(%s)|(%s)|(%s)|(%s)|(%s)|([%s%s%s])',
            TokenNumber::getRegex(),
            TokenStringDoubleQuoted::getRegex(),
            TokenStringSingleQuoted::getRegex(),
            TokenFunction::getRegex(),
            TokenVariable::getRegex(),
            TokenLeftBracket::getRegex(),
            TokenRightBracket::getRegex(),
            TokenComma::getRegex()
        );
        $s .= $operatorsRegex . '/i';

        return $s;
    }

    /**
     * @param  string $token
     * @return InterfaceToken
     * @throws UnknownTokenException
     * @throws UnknownFunctionException
     */
    public function createToken($token)
    {
        if (is_numeric($token)) {
            return new TokenNumber($token);
        }

        if ($token == '(') {
            return new TokenLeftBracket();
        }

        if ($token == ')') {
            return new TokenRightBracket();
        }

        if ($token[0] == '"') {
            return new TokenStringDoubleQuoted(str_replace('"', '', $token));
        }

        if ($token[0] == "'") {
            return new TokenStringSingleQuoted(str_replace("'", '', $token));
        }

        if ($token == ',') {
            return new TokenComma();
        }

        foreach ($this->operators as $operator) {
            $regex = sprintf('/%s/i', $operator::getRegex());
            if (preg_match($regex, $token)) {
                $op = new $operator;
                return $op->setDivisionByZeroException($this->getDivisionByZeroException());
            }
        }

        $regex = sprintf('/%s/i', TokenVariable::getRegex());
        if (preg_match($regex, $token)) {
            return new TokenVariable(substr($token, 1));
        }

        $regex = sprintf('/%s/i', TokenFunction::getRegex());
        if (preg_match($regex, $token)) {
            if (isset($this->functions[$token])) {
                return new TokenFunction($this->functions[$token]);
            } else {
                throw new UnknownFunctionException($token);
            }
        }

        throw new UnknownTokenException($token);
    }
}
