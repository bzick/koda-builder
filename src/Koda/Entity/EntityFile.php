<?php

namespace Koda\Entity;


use Koda\EntityScanner;
use Koda\Error\UnexpectedTokenException;
use Koda\FS;
use Koda\Line;
use Koda\Project;
use Koda\Tokenizer;
use Koda\ToolKit;

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

    public function __construct($path, Project $project) {
        $this->path = $path;
        $this->project = $project;
    }

    public function getBasePath() {
        return str_replace($this->project->root.'/', '', $this->path);
    }

    public function line($no) {
        return new Line($this, $no);
    }

    public function scan() {
        require_once $this->path;
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
                $constant = new EntityConstant($_ns.$name);
                $constant->setValue(constant($_ns.$name));
                $constant->setLine($this->line($tokens->getLine()));
                $this->constants[$_ns.$name] = $constant;
                $tokens->forwardTo(';')->next();
            } elseif($tokens->is(T_FUNCTION)) {
                $name = $tokens->next()->get(T_STRING);
                $function = new EntityFunction($_ns.$name);
                $function->setLine($this->line($tokens->getLine()));
                $function->setAliases($aliases);
                $this->parseCallable($function, new \ReflectionFunction($function->name));
                $function->setBody($tokens->forwardTo('{')->getScope());
                $tokens->next();
                $this->functions[$function->name] = $function;
            } elseif($tokens->is(T_FINAL, T_ABSTRACT, T_INTERFACE, T_TRAIT, T_CLASS)) {
                $tokens->forwardTo(T_STRING);
                $name = $tokens->current();
                $class = new EntityClass($_ns.$name);
                $ref           = new \ReflectionClass($class->name);
                $doc           = $ref->getDocComment();
//                if($name == "NamesInterface") {
//                    drop($ref);
//                }
                if($ref->isInterface()) {
                    $class->addFlag(Flags::IS_INTERFACE);
                } elseif($ref->isTrait()) {
                    $class->addFlag(Flags::IS_TRAIT);
                } else {
                    $class->addFlag(Flags::IS_CLASS);
                }
                if($ref->isAbstract()) {
                    $class->addFlag(Flags::IS_ABSTRACT);
                } elseif($ref->isFinal()) {
                    $class->addFlag(Flags::IS_FINAL);
                }
                if($doc) {
                    $info = ToolKit::parseDoc($doc);
                    $class->setDescription($info['desc']);
                    $class->addOptions($info['options']);
                }
	            $class->setAliases($aliases);
                $class->setLine($this->line($tokens->getLine()));
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
                            $constant = new EntityConstant($class->name.'::'.$tokens->next()->get(T_STRING));
                            $constant->setValue(constant($constant->name));
                            $constant->setLine(new Line($this, $tokens->getLine()));
                            $class->addConstant($constant);
                            break;
                        case T_VARIABLE:
                            $property = new EntityProperty(ltrim($tokens->getAndNext(), '$'));
                            $ref = new \ReflectionProperty($class->name, $property->name);
                            $doc = $ref->getDocComment();
                            if($doc) {
                                $property->setDescription(ToolKit::parseDoc($doc)['desc']);
                            }
                            if($ref->isPrivate()) {
                                $property->addFlag(Flags::IS_PRIVATE);
                            } elseif($ref->isProtected()) {
                                $property->addFlag(Flags::IS_PROTECTED);
                            } else {
                                $property->addFlag(Flags::IS_PUBLIC);
                            }

                            if($ref->isStatic()) {
                                $property->addFlag(Flags::IS_STATIC);
                            }

                            if($ref->isDefault()) {
                                $property->setValue($ref->getDeclaringClass()->getDefaultProperties()[$property->name]);
                            }
                            $class->addProperty($property);
                            break;
                        case T_FUNCTION:
                            $method = new EntityMethod($name.'::'.$tokens->next()->get(T_STRING));
                            $method->setLine($this->line($tokens->getLine()));

                            $this->parseCallable($method, $ref = new \ReflectionMethod($class->name, $method->short));
                            if($ref->isPrivate()) {
                                $method->addFlag(Flags::IS_PRIVATE);
                            } elseif($ref->isProtected()) {
                                $method->addFlag(Flags::IS_PROTECTED);
                            } else {
                                $method->addFlag(Flags::IS_PUBLIC);
                            }

                            if($ref->isStatic()) {
                                $method->addFlag(Flags::IS_STATIC);
                            }

                            if($ref->isAbstract()) {
                                $method->addFlag(Flags::IS_ABSTRACT);
                                $method->addFlag(Flags::IS_ABSTRACT_IMPLICIT);
                            } elseif($ref->isFinal()) {
                                $method->addFlag(Flags::IS_FINAL);
                            }

                            if(isset($method->options['deprecated'])) {
                                $method->addFlag( Flags::IS_DEPRECATED);
                            }
                            $tokens->forwardTo(')')->next();
                            if($tokens->is('{')) {
                                $method_body = $tokens->getScope();
                                $method->setBody($method_body);
                            }
                            $tokens->next();
                            $class->addMethod($method);
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

    /**
     *
     * @param EntityFunction $callable
     * @param \ReflectionFunctionAbstract $reflection
     * @return $this
     */
    public static function parseCallable(EntityFunction $callable, \ReflectionFunctionAbstract $reflection) {
        $doc         = $reflection->getDocComment();
        $params      = [];

        if($doc) {
            $info = ToolKit::parseDoc($doc);
            $callable->setDescription($info['desc']);
            $callable->setReturnInfo($info['return']['type'], $reflection->returnsReference(), $info['return']['desc']);
            $callable->setOptions($info['options']);
            $params = $info["params"];
        }
        /* @var \ReflectionParameter[] $params */
        foreach($reflection->getParameters() as $param) {
            $argument = new EntityArgument($param->name);
            if(isset($params[ $param->name ]["desc"])) {
                $argument->description = $params[ $param->name ]["desc"];
            }
            $argument->setLine($callable->getLine());
            $argument->setOptional($param->isOptional());
            $argument->setNullAllowed($param->allowsNull());
            $argument->setValue($param->isDefaultValueAvailable() ? $param->getDefaultValue() : null, $param->isPassedByReference());
            $argument->setByRef($param->isPassedByReference());
            /** @var \ReflectionParameter $param */
            if($param->isArray()) {
                $argument->setCast(Types::ARR);
            }
            if($c = $param->getClass()) {
                $argument->setCast(Types::OBJECT, $c->name);
            } elseif(isset($doc_params[ $param->name ])) {
                $_type = $doc_params[ $param->name ]["type"];
                if(strpos($_type, "|") || $_type === "mixed") { // multiple types or mixed
                    $argument->setCast(Types::MIXED);
                } else {
                    if(strpos($_type, "[]")) {
                        $argument->setCast(Types::ARR, rtrim($_type, '[]'));
                    }
                    if(isset(Types::$native[$_type])) {
                        $argument->setCast(Types::getType($_type));
                    } else {
                        $argument->setCast(Types::OBJECT, ltrim($_type,'\\'));
                    }
                }
            } else {
                $argument->warning("not documented");
                $argument->setCast(Types::MIXED);
            }
            $callable->pushArgument($argument);
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