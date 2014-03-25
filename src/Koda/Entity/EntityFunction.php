<?php

namespace Koda\Entity;


use PhpParser\Lexer;
use PhpParser\Parser;

class EntityFunction {

    private static $_aliases = [
        "integer" => "int",
        "str" => "string",
        "double" => "float"
    ];

    /**
     * @var array of native types with priorities
     */
    private static $_native = array(
        "int" => 9,
        "bool" => 7,
        "float" => 8,
        "string" => 10,
        "array" => 6,
        "NULL" => 1,
        "resource" => 5,
        "callable" => 10
    );

    public $strict = true;
    public $name;
    public $description;
    public $return;
    public $options;
    public $ref       = false;
    public $generator = false;
    public $short;
    public $ns;
    public $line;
    public $aliases;
    public $body;
    public $arguments;
    public $statics;
    public $stmts;

    /**
     * @param $name
     * @param $aliases
     * @param $line
     */
    public function __construct($name, $aliases, $line) {
        $this->aliases = $aliases;
        $this->name = $name;
        $this->line = $line;
        $func = new \ReflectionFunction($name);
        $this->short = $func->getShortName();
        $this->ns = $func->getNamespaceName();
    }

    /**
     * @param string $body
     */
    public function setBody($body) {
        $this->body = $body;
        $parser = new Parser(new Lexer);
        $this->stmts = $parser->parse('<?php'.$this->body);
    }


    /**
     *
     * @param \ReflectionMethod $method
     * @return array
     */
    public  function scan(\ReflectionMethod $method) {
        $info = array(
            "desc" => "",
            "args" => null,
            "files" => false,
            "return" => null,
            "options" => array(),
            "method" => $method->class."::".$method->name
        );
        $doc = $method->getDocComment();
        $doc_params = array();

        if($doc) {
            $doc = preg_replace('/^\s*(\*\s*)+/mS', '', trim($doc, "*/ \t\n\r"));
            if(strpos($doc, "@") !== false) {
                $doc = explode("@", $doc, 2);
                if($doc[0] = trim($doc[0])) {
                    $this->description = $doc[0];
                }
                if($doc[1]) {
                    foreach(preg_split('/\r?\n@/mS', $doc[1]) as $param) {
                        $param = preg_split('/\s+/', $param, 2);
                        if(!isset($param[1])) {
                            $param[1] = "";
                        }
                        switch(strtolower($param[0])) {
                            case 'description':
                                if(empty($info["desc"])) {
                                    $this->description = $param[1];
                                }
                                break;
                            case 'param':
                                if(preg_match('/^(.*?)\s*\$(\w+)\s*?/mS', $param[1], $matches)) {
                                    $doc_params[ $matches[2] ] = array(
                                        "type" => $matches[1],
                                        "desc" => trim(substr($param[1], strlen($matches[0])))
                                    );

                                }
                                break;
                            case 'return':
                                if(preg_match('/^(.*?)\s*$/m', $param[1], $matches)) {
                                    $this->return["type"] = $matches[1];
                                    $this->return["desc"] = $matches[2];
                                }
                                break;
                            default:
                                if(isset($this->options[ $param[0] ])) {
                                    if(!is_array($info["options"][ $param[0] ])) {
                                        $this->options[ $param[0] ] = array($info["options"][ $param[0] ]);
                                    }
                                    $this->options[ $param[0] ][] = $param[1];
                                } else {
                                    $this->options[ $param[0] ] = $param[1];
                                }
                        }
                    }
                }
            } else {
                $info["desc"] = $doc;
            }

        }
        $args = array();
        foreach($method->getParameters() as $param) {
            $argument = new EntityArgument($this, $param->name);
            if(isset($doc_params[ $param->name ])) {
                $argument->description = $doc_params[ $param->name ];
            }
            $argument->is_optional = $param->isOptional();
            $argument->default_value = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null;
            $argument->position = $param->getPosition();
            /** @var \ReflectionParameter $param */
            if($param->isArray()) {
                $this->strict = false;
                $argument->type = 'array';
            }
            if($c = $param->getClass()) {
                $this->strict = false;
                $argument->type = "object";
                $argument->instance_of = $c->name;
            } elseif(isset($doc_params[ $param->name ])) {
                $_type = $doc_params[ $param->name ]["type"];
                if(strpos($_type, "|")) { // multiple types
                    throw new \LogicException("Multiple types unsupported yet");
//                    foreach(explode("|", $_type) as $type) {
//                        $type = trim($type);
//                        if(strpos($_type, "[]")) {
//                            $type = trim($type, '[]');
//                            $arg["multiple"] = true;
//                        }
//                        if(isset(self::$_aliases[ $type ])) {
//                            $type = self::$_aliases[ $type ];
//                        } elseif($_type === "mixed") {
//                            $arg["type"] = null;
//                            continue;
//                        }
//                        if(isset(self::$_native[$type])) {
//                            $arg["type"][ $type ] = self::$_native[$type];
//                        } else {
//                            $arg["type"]["object"] = 1;
//                            $arg["type"]["class"][] = $type;
//                        }
//
//                    }
//                    arsort($arg["type"]); // sort by types (@see self::$_native)
                } elseif($_type === "mixed") {
                    $this->strict = false;
                    $this->type = null;
                } else {
                    if(strpos($_type, "[]")) {
                        $this->strict = false;
                        $this->type = 'array';
                        $this->hint = rtrim($_type, '[]');
                    }
                    if(isset(self::$_native[$_type])) {
                        $this->type = $_type;
                    } else {
                        $_type = ltrim($_type,'\\');
                        $this->type = "object";
                        $this->instance_of =  $_type;
                    }
                }
            } else {
                $this->log(LOG_WARNING, 'Undocumented argument $'.$argument->name.' in '.$this->name.' [error.method.arg.undocumented]');
                $this->strict = false;
            }
            $this->arguments[$argument->name] = $argument;
            $args[ $param->name ] = $arg;
        }
        $info["args"] = $args;
        return $info;
    }
} 