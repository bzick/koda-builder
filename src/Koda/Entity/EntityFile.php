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
     * @var string
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
        $ns_bracket = false;
        $aliases    = [];
        $tokens = new Tokenizer(FS::get($this->path));
        while($tokens->valid()) {
            if($tokens->is(T_NAMESPACE)) {
                $ns         = "";
                $_ns        = "";
                $tokens->next();
                if($tokens->is(T_STRING)) {
                    $ns = $this->_parseName($tokens);
                    if($tokens->is('{')) {
                        $tokens->skip();
                        $ns_bracket = true;
                    } else {
                        $tokens->skipIf(';');
                    }
                    $_ns = $ns.'\\';
                } elseif($tokens->is('{')) {
                    $ns_bracket = true;
                    $tokens->next();
                }
            } elseif($tokens->is(T_USE)) {
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
                $tokens->next();
                if($tokens->is(T_EXTENDS)) { // process 'extends' keyword
                    do {
                        $tokens->next();
                        $root = $tokens->is(T_NS_SEPARATOR);
                        $parent = $this->_parseName($tokens);
                        if($root) { // extends from root namespace
                            $class->setParent($parent, $class->isInterface());
                        } elseif(isset($aliases[$parent])) {
                            $class->setParent($aliases[$parent], $class->isInterface());
                        } else {
                            $class->setParent($_ns.$parent, $class->isInterface());
                        }
                    } while($tokens->is(','));
                }
                if($tokens->is(T_IMPLEMENTS)) { // process 'implements' keyword
                    do {
                        $tokens->next();
                        $root = $tokens->is(T_NS_SEPARATOR);
                        $parent = $this->_parseName($tokens);
                        if($root) { // extends from root namespace
                            $class->addInterface($parent);
                        } elseif(isset($aliases[$parent])) {
                            $class->addInterface($aliases[$parent]);
                        } else {
                            $class->addInterface($_ns.$parent);
                        }
                    } while($tokens->is(','));
                }
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
                            $tokens->forwardTo(')')->next();
                            $method = $class->addMethod($method_name, [$this, $method_line]);
                            if($tokens->is('{')) {
                                $method_body = $tokens->getScope();
                                $method->setBody($method_body);
                            }
                            $method->scan();
                            $tokens->next();
                            break;
                        case '{':   // use traits scope
                            $tokens->forwardTo('}')->next();
                            break;
                        case '}':   // end of class
                            $tokens->next();
                            $this->classes[$class->name] = $class;
                            break 2;

                    }
                }
            } elseif($tokens->is('}') && $ns_bracket) {
                $tokens->next();
                $ns_bracket = false;
            } else {
                drop($tokens->curr);
                if($tokens->valid()) {
                    throw new UnexpectedTokenException($tokens);
                }
                break;
            }
        }

    }

    public function __toString() {
        return $this->path;
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