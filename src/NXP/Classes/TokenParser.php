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

class TokenParser
{
    const DIGIT         = 'DIGIT';
    const CHAR          = 'CHAR';
    const SPECIAL_CHAR  = 'SPECIAL_CHAR';
    const LEFT_BRACKET  = 'LEFT_BRACKET';
    const RIGHT_BRACKET = 'RIGHT_BRACKET';
    const SPACE         = 'SPACE';

    private $terms = array(
        self::DIGIT         => '[0-9\.]',
        self::CHAR          => '[a-z_]',
        self::SPECIAL_CHAR  => '[\!\@\#\$\%\^\&\*\/\|\-\+\=\~]',
        self::LEFT_BRACKET  => '\(',
        self::RIGHT_BRACKET => '\)',
        self::SPACE         => '\s'
    );

    const ERROR_STATE = 'ERROR_STATE';

    private $transitions = array(
        Token::NOTHING => array(
            self::DIGIT         => Token::NUMBER,
            self::CHAR          => Token::STRING,
            self::SPECIAL_CHAR  => Token::OPERATOR,
            self::LEFT_BRACKET  => Token::LEFT_BRACKET,
            self::RIGHT_BRACKET => Token::RIGHT_BRACKET,
            self::SPACE         => Token::NOTHING
        ),
        Token::STRING => array(
            self::DIGIT         => Token::STRING,
            self::CHAR          => Token::STRING,
            self::SPECIAL_CHAR  => Token::OPERATOR,
            self::LEFT_BRACKET  => Token::LEFT_BRACKET,
            self::RIGHT_BRACKET => Token::RIGHT_BRACKET,
            self::SPACE         => Token::NOTHING
        ),
        Token::NUMBER => array(
            self::DIGIT         => Token::NUMBER,
            self::CHAR          => self::ERROR_STATE,
            self::SPECIAL_CHAR  => Token::OPERATOR,
            self::LEFT_BRACKET  => Token::LEFT_BRACKET,
            self::RIGHT_BRACKET => Token::RIGHT_BRACKET,
            self::SPACE         => Token::NOTHING
        ),
        Token::OPERATOR => array(
            self::DIGIT         => Token::NUMBER,
            self::CHAR          => Token::STRING,
            self::SPECIAL_CHAR  => Token::OPERATOR,
            self::LEFT_BRACKET  => Token::LEFT_BRACKET,
            self::RIGHT_BRACKET => Token::RIGHT_BRACKET,
            self::SPACE         => Token::NOTHING
        ),
        self::ERROR_STATE => array(
            self::DIGIT         => self::ERROR_STATE,
            self::CHAR          => self::ERROR_STATE,
            self::SPECIAL_CHAR  => self::ERROR_STATE,
            self::LEFT_BRACKET  => self::ERROR_STATE,
            self::RIGHT_BRACKET => self::ERROR_STATE,
            self::SPACE         => self::ERROR_STATE
        ),
        Token::LEFT_BRACKET => array(
            self::DIGIT         => Token::NUMBER,
            self::CHAR          => Token::STRING,
            self::SPECIAL_CHAR  => Token::OPERATOR,
            self::LEFT_BRACKET  => Token::LEFT_BRACKET,
            self::RIGHT_BRACKET => Token::RIGHT_BRACKET,
            self::SPACE         => Token::NOTHING
        ),
        Token::RIGHT_BRACKET => array(
            self::DIGIT         => Token::NUMBER,
            self::CHAR          => Token::STRING,
            self::SPECIAL_CHAR  => Token::OPERATOR,
            self::LEFT_BRACKET  => Token::LEFT_BRACKET,
            self::RIGHT_BRACKET => Token::RIGHT_BRACKET,
            self::SPACE         => Token::NOTHING
        ),
    );

    private $accumulator = '';

    private $state = Token::NOTHING;

    private $queue = null;

    public function __construct()
    {
        $this->queue = new \SplQueue();
    }

    /**
     * Tokenize math expression
     * @param $expression
     * @return \SplQueue
     * @throws \Exception
     */
    public function tokenize($expression)
    {
        $oldState = null;
        for ($i=0; $i<strlen($expression); $i++) {
            $char = substr($expression, $i, 1);
            $class = $this->getSymbolType($char);
            $oldState = $this->state;
            $this->state = $this->transitions[$this->state][$class];
            if ($this->state == self::ERROR_STATE) {
                throw new \Exception("Parse expression error at $i column (symbol '$char')");
            }
            $this->addToQueue($oldState);
            $this->accumulator .= $char;
        }
        if (!empty($this->accumulator)) {
            $token = new Token($this->state, $this->accumulator);
            $this->queue->push($token);
        }

        return $this->queue;
    }

    /**
     * @param $symbol
     * @return string
     * @throws \Exception
     */
    private function getSymbolType($symbol)
    {
        foreach ($this->terms as $class => $regex) {
            if (preg_match("/$regex/i", $symbol)) {
                return $class;
            }
        }

        throw new \Exception("Unknown char '$symbol'");
    }

    /**
     * @param $oldState
     */
    private function addToQueue($oldState)
    {
        if ($oldState == Token::NOTHING) {
            $this->accumulator = '';

            return;
        }

        if (($this->state != $oldState) || ($oldState == Token::LEFT_BRACKET) || ($oldState == Token::RIGHT_BRACKET)) {
            $token = new Token($oldState, $this->accumulator);
            $this->queue->push($token);
            $this->accumulator = '';
        }
    }
}
