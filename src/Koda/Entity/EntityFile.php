<?php

namespace Koda\Entity;


use Koda\EntityScanner;
use Koda\Error\UnexpectedTokenException;
use Koda\FS;
use Koda\Tokenizer;

class EntityFile {

    public static $keywords = [
        'public' => 1
    ];

    /**
     * @var \SplFileInfo
     */
    public $path;

    public $classes   = [];
    public $functions = [];
    public $constants = [];

    public function __construct($path) {
        $this->path = $path;
        require $path;
    }

    public function scan() {
        $ns         = "";
        $_ns        = "";
        $brackets   = 0;
        $aliases    = [];
        $tokens = new Tokenizer(FS::get($this->path));
        if($tokens->is(T_NAMESPACE)) {
            $_ns = $this->_parseName($tokens->next());
            if($tokens->is('{')) {
                $tokens->skip();
                $brackets++;
            } else {
                $tokens->skipIf(';');
            }
            $_ns = $ns.'\\';
        }
        while($tokens->valid()) {
            if($tokens->is(T_USE)) {
                do {
                    $tokens->next();
                    $name = $this->_parseName($tokens);
                    if($tokens->is(T_AS)) {
                        $aliases[$tokens->next()->get(T_STRING)] = $name;
                        $tokens->next();
                    } else {
                        if(strpos($name, '\\') === false) {
                            $aliases[$name] = $name;
                        } else {
                            $aliases[ltrim('\\', strrchr($name, '\\'))] = $name;
                        }
                    }
                } while($tokens->is(','));
                $tokens->need(';')->next();
            } elseif($tokens->is(T_CONST)) {
                $name = $tokens->next()->get(T_STRING);
                $this->constants[$_ns.$name] = new EntityConstant($_ns.$name, constant($_ns.$name), [$this, $tokens->getLine()]);
                $tokens->forwardTo(';')->next();
            } elseif($tokens->is(T_FUNCTION)) {
                $name = $tokens->next()->get(T_STRING);
                $this->functions[$_ns.$name] = new EntityFunction($_ns.$name, $aliases, [$this, $tokens->getLine()]);
                $tokens->forwardTo('{')->forwardToEndScope()->next();
            } elseif($tokens->is(T_FINAL, T_ABSTRACT, T_INTERFACE, T_TRAIT, T_CLASS)) {
                $tokens->forwardTo(T_STRING);
                $name = $tokens->current();
                $this->classes[$_ns.$name] = new EntityClass($_ns.$name, $aliases, [$this, $tokens->getLine()]);
                $tokens->forwardTo('{')->forwardToEndScope()->next();
            } else {
                break;
            }
        }

        if($brackets) {
            $tokens->need('}');
        }
    }


    private function _parseName(Tokenizer $tokens) {
        $tokens->skipIf(T_NS_SEPARATOR);
        $name = $tokens->get(T_STRING);
        while($tokens->next()->is(T_NS_SEPARATOR)) {
            $name .= '\\'.$tokens->next()->get(T_STRING);
        }

        return ltrim($name, '\\');
    }
} 