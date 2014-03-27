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
            $ns = $this->_parseName($tokens->next());
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
                $function = new EntityFunction($_ns.$name, $aliases, [$this, $tokens->getLine()]);
                $function->setBody($tokens->forwardTo('{')->getScope())->scan();
                $tokens->next();
                $this->functions[$function->name] = $function;
            } elseif($tokens->is(T_FINAL, T_ABSTRACT, T_INTERFACE, T_TRAIT, T_CLASS)) {
                $tokens->forwardTo(T_STRING);
                $name = $tokens->current();
                $class = new EntityClass($_ns.$name, $aliases, [$this, $tokens->getLine()]);
                $tokens->forwardTo('{')->next();
                while($tokens->forwardTo(T_CONST, T_FUNCTION, '{', '}', T_VARIABLE) && $tokens->valid()) {
                    switch($tokens->key()) {
                        case T_CONST:
                            $class->addConstant($tokens->next()->get(T_STRING), [$this, $tokens->getLine()]);
                            break;
                        case T_VARIABLE:
                            $class->addProperty(ltrim($tokens->getAndNext(), '$'), [$this, $tokens->getLine()]);
                            break;
                        case T_FUNCTION:
                            $method_name = $tokens->next()->get(T_STRING);
                            $method_line = $tokens->getLine();
                            $method_body = $tokens->forwardTo('{')->getScope();
                            $method = $class->addMethod($method_name, [$this, $method_line]);
                            $method->setBody($method_body)->scan();
                            $tokens->next();
                            break;
                        case '{':   // use traits scope
                            $tokens->forwardTo('}')->next();
                            break;
                        case '}':   // end of class
                            $tokens->next();
                            $this->classes[$class->name] = $class;
                            break 3;

                    }
                }
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