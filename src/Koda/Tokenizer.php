<?php
/*
 * This file is part of Fenom.
 *
 * (c) 2013 Ivan Shalganov
 *
 * For the full copyright and license information, please view the license.md
 * file that was distributed with this source code.
 */
namespace Koda;

use Koda\Error\UnexpectedTokenException;

/**
 * for PHP <5.4 compatible
 */
defined('T_INSTEADOF') || define('T_INSTEADOF', 341);
defined('T_TRAIT') || define('T_TRAIT', 355);
defined('T_TRAIT_C') || define('T_TRAIT_C', 365);
/**
 * for PHP <5.5 compatible
 */
defined('T_YIELD') || define('T_YIELD', 267);

/**
 * Each token have structure
 *  - Token (constant T_* or text)
 *  - Token name (textual representation of the token)
 *  - Whitespace (whitespace symbols after token)
 *  - Line number of the token
 *
 * @see http://php.net/tokenizer
 * @property array $prev the previous token
 * @property array $curr the current token
 * @property array $next the next token
 *
 * @package    Fenom
 * @author     Ivan Shalganov <a.cobest@gmail.com>
 */
class Tokenizer
{
    const TOKEN = 0;
    const TEXT = 1;
    const WHITESPACE = 2;
    const LINE = 3;

    public $tokens;
    public $p = 0;
    public $quotes = 0;
    public $options;
    private $_max = 0;
    private $_last_no = 0;

    /**
     * Special tokens
     * @var array
     */
    private static $spec = array(
        'true' => 1, 'false' => 1, 'null' => 1, 'TRUE' => 1, 'FALSE' => 1, 'NULL' => 1
    );

    /**
     * @param $query
     */
    public function __construct($query)
    {
        $tokens = array(-1 => array(\T_WHITESPACE, '', '', 1));
        $_tokens = token_get_all($query);
        $line = 1;
        array_shift($_tokens);
        $i = 0;
        $spaced = $comment = "";
        foreach ($_tokens as $token) {
            if (is_string($token)) {
                if ($token === '"' || $token === "'" || $token === "`") {
                    $this->quotes++;
                }
                $tokens[] = array(
                    $token,
                    $token,
                    $spaced,
                    $line,
                    $comment
                );
                $i++;
                $comment = $spaced = "";
            } elseif ($token[0] === \T_WHITESPACE) {
                $spaced .= $token[1];
            } elseif ($token[0] === \T_COMMENT || $token[0] === \T_DOC_COMMENT) {
                $comment = $token[1];
            } else {
                $tokens[] = array(
                    $token[0],
                    $token[1],
                    $spaced,
                    $line = $token[2],
                    $comment,
                    token_name($token[0]) // debug
                );
                $i++;
                $comment = $spaced = "";
            }

        }
        unset($tokens[-1]);
        $this->tokens = $tokens;
        $this->_max = count($this->tokens) - 1;
        $this->_last_no = $this->tokens[$this->_max][3];
    }

    /**
     * Is incomplete mean some string not closed
     *
     * @return int
     */
    public function isIncomplete()
    {
        return ($this->quotes % 2) || ($this->tokens[$this->_max][0] === T_ENCAPSED_AND_WHITESPACE);
    }

    /**
     * Return the current element
     *
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        return $this->curr[1];
    }

    /**
     * Move forward to next element
     *
     * @link http://php.net/manual/en/iterator.next.php
     * @return Tokenizer
     */
    public function next()
    {
        if ($this->p > $this->_max) {
            return $this;
        }
        $this->p++;
        unset($this->prev, $this->curr, $this->next);
        return $this;
    }

    /**
     * Check token type. If token type is one of expected types return true. Otherwise return false
     *
     * @param array $expects
     * @param string|int $token
     * @return bool
     */
    private function _valid($expects, $token)
    {
        foreach ($expects as $expect) {
            if (is_string($expect) || $expect < 1000) {
                if ($expect === $token) {
                    return true;
                }
            } else {

                if (isset(self::$macros[$expect][$token])) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * If the next token is a valid one, move the position of cursor one step forward. Otherwise throws an exception.
     * @param array $tokens
     * @throws UnexpectedTokenException
     * @return mixed
     */
    public function _next($tokens)
    {
        $this->next();
        if (!$this->curr) {
            throw new UnexpectedTokenException($this, $tokens);
        }
        if ($tokens) {
            if ($this->_valid($tokens, $this->key())) {
                return;
            }
        } else {
            return;
        }
        throw new UnexpectedTokenException($this, $tokens);
    }

    /**
     * Fetch next specified token or throw an exception
     * @return mixed
     */
    public function getNext( /*int|string $token1, int|string $token2, ... */)
    {
        $this->_next(func_get_args());
        return $this->current();
    }

    /**
     * @param $token
     * @return bool
     */
    public function isNextToken($token)
    {
        return $this->next ? $this->next[1] == $token : false;
    }

    /**
     * Return token and move pointer
     * @return mixed
     * @throws UnexpectedTokenException
     */
    public function getAndNext( /* $token1, ... */)
    {
        if ($this->curr) {
            $cur = $this->curr[1];
            $this->next();
            return $cur;
        } else {
            throw new UnexpectedTokenException($this, func_get_args());
        }
    }

    /**
     * Check if the next token is one of the specified.
     * @param $token1
     * @return bool
     */
    public function isNext($token1 /*, ...*/)
    {
        return $this->next && $this->_valid(func_get_args(), $this->next[0]);
    }

    /**
     * Check if the current token is one of the specified.
     * @param $token1
     * @return bool
     */
    public function is($token1 /*, ...*/)
    {
        return $this->curr && $this->_valid(func_get_args(), $this->curr[0]);
    }

    /**
     * Check if the previous token is one of the specified.
     * @param $token1
     * @return bool
     */
    public function isPrev($token1 /*, ...*/)
    {
        return $this->prev && $this->_valid(func_get_args(), $this->prev[0]);
    }

    /**
     * Get specified token
     *
     * @param string|int $token1
     * @throws UnexpectedTokenException
     * @return mixed
     */
    public function get($token1 /*, $token2 ...*/)
    {
        if ($this->curr && $this->_valid(func_get_args(), $this->curr[0])) {
            return $this->curr[1];
        } else {
            throw new UnexpectedTokenException($this, func_get_args());
        }
    }

    /**
     * Step back
     * @return Tokenizer
     */
    public function back()
    {
        if ($this->p === 0) {
            return $this;
        }
        $this->p--;
        unset($this->prev, $this->curr, $this->next);
        return $this;
    }

    /**
     * @param $token1
     * @return bool
     */
    public function hasBackList($token1 /*, $token2 ...*/)
    {
        $tokens = func_get_args();
        $c = $this->p;
        foreach ($tokens as $token) {
            $c--;
            if ($c < 0 || $this->tokens[$c][0] !== $token) {
                return false;
            }
        }
        return true;
    }

    /**
     * Lazy load properties
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        switch ($key) {
            case 'curr':
                return $this->curr = ($this->p <= $this->_max) ? $this->tokens[$this->p] : null;
            case 'next':
                return $this->next = ($this->p + 1 <= $this->_max) ? $this->tokens[$this->p + 1] : null;
            case 'prev':
                return $this->prev = $this->p ? $this->tokens[$this->p - 1] : null;
            default:
                return $this->$key = null;
        }
    }

    public function count()
    {
        return $this->_max;
    }

    /**
     * Return the key of the current element
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->curr ? $this->curr[0] : null;
    }

    /**
     * Checks if current position is valid
     * @return boolean The return value will be casted to boolean and then evaluated.
     *       Returns true on success or false on failure.
     */
    public function valid()
    {
        return (bool)$this->curr;
    }

    /**
     * Get token name
     * @static
     * @param int|string $token
     * @return string
     */
    public static function getName($token)
    {
        if (is_string($token)) {
            return $token;
        } elseif (is_integer($token)) {
            return token_name($token);
        } elseif (is_array($token)) {
            return token_name($token[0]);
        } else {
            return null;
        }
    }

    /**
     * Skip specific token or throw an exception
     *
     * @throws UnexpectedTokenException
     * @return Tokenizer
     */
    public function skip( /*$token1, $token2, ...*/)
    {
        if (func_num_args()) {
            if ($this->_valid(func_get_args(), $this->curr[0])) {
                $this->next();
                return $this;
            } else {
                throw new UnexpectedTokenException($this, func_get_args());
            }
        } else {
            $this->next();
            return $this;
        }
    }

    /**
     * Skip specific token or do nothing
     *
     * @param int|string $token1
     * @return Tokenizer
     */
    public function skipIf($token1 /*, $token2, ...*/)
    {
        if ($this->_valid(func_get_args(), $this->curr[0])) {
            $this->next();
        }
        return $this;
    }

    /**
     * Check current token's type
     *
     * @param int|string $token1
     * @return Tokenizer
     * @throws UnexpectedTokenException
     */
    public function need($token1 /*, $token2, ...*/)
    {
        if ($this->_valid(func_get_args(), $this->curr[0])) {
            return $this;
        } else {
            throw new UnexpectedTokenException($this, func_get_args());
        }
    }

    /**
     * Get tokens near current position
     * @param int $before count tokens before current token
     * @param int $after count tokens after current token
     * @return array
     */
    public function getSnippet($before = 0, $after = 0)
    {
        $from = 0;
        $to = $this->p;
        if ($before > 0) {
            if ($before > $this->p) {
                $from = $this->p;
            } else {
                $from = $before;
            }
        } elseif ($before < 0) {
            $from = $this->p + $before;
            if ($from < 0) {
                $from = 0;
            }
        }
        if ($after > 0) {
            $to = $this->p + $after;
            if ($to > $this->_max) {
                $to = $this->_max;
            }
        } elseif ($after < 0) {
            $to = $this->_max + $after;
            if ($to < $this->p) {
                $to = $this->p;
            }
        } elseif ($this->p > $this->_max) {
            $to = $this->_max;
        }
        $code = array();
        for ($i = $from; $i <= $to; $i++) {
            $code[] = $this->tokens[$i];
        }

        return $code;
    }

    /**
     * Return snippet as string
     * @param int $before
     * @param int $after
     * @return string
     */
    public function getSnippetAsString($before = 0, $after = 0)
    {
        $str = "";
        foreach ($this->getSnippet($before, $after) as $token) {
            $str .= $token[2] . $token[1];
        }
        return trim(str_replace("\n", '↵', $str));
    }

    /**
     * Check if current is special value: true, TRUE, false, FALSE, null, NULL
     * @return bool
     */
    public function isSpecialVal()
    {
        return isset(self::$spec[$this->current()]);
    }

    /**
     * Check if the token is last
     * @return bool
     */
    public function isLast()
    {
        return $this->p === $this->_max;
    }

    /**
     * Move pointer to the end
     */
    public function end()
    {
        $this->p = $this->_max;
        unset($this->prev, $this->curr, $this->next);
        return $this;
    }

    /**
     * Return line number of the current token
     * @return mixed
     */
    public function getLine()
    {
        return $this->curr ? $this->curr[3] : $this->_last_no;
    }

    /**
     * Is current token whitespaced, means previous token has whitespace characters
     * @return bool
     */
    public function isWhiteSpaced()
    {
        return $this->prev ? (bool)$this->prev[2] : false;
    }

    public function getWhitespace()
    {
        return $this->curr ? $this->curr[2] : false;
    }

    /**
     * Seek to custom element
     * @param int $p
     * @return $this
     */
    public function seek($p)
    {
        $this->p = $p;
        unset($this->prev, $this->curr, $this->next);
        return $this;
    }

    public function forwardTo($token1 /*, $token2, ...*/) {
        while ($this->valid() && !$this->_valid(func_get_args(), $this->curr[0])) {
            $this->next();
        }

        return $this;
    }

    public function forwardToEndScope() {
        $brackets = 0;
        while($this->valid()) {
            if($this->is('{')) {
                $brackets++;
            } elseif($this->is('}')) {
                $brackets--;
                if(!$brackets) {
                    return $this;
                }
            }
            $this->next();
        }

        throw new UnexpectedTokenException($this);
    }

    public function getScope() {
        $p = $this->p;
        $this->forwardToEndScope();
        $str = "";
        $slice = array_slice($this->tokens, $p + 1, $this->p - $p - 1);
        foreach ($slice as $token) {
            $str .= $token[2] . $token[1];
        }
        return $str;
    }

    public function forwardWhile($token1 /*, $token2, ...*/) {
        while ($this->valid() && $this->_valid(func_get_args(), $this->curr[0])) {
            $this->next();
        }

        return $this;
    }
}
