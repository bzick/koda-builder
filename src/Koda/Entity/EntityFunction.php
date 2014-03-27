<?php

namespace Koda\Entity;


use Koda\EntityInterface;
use PhpParser\Lexer;
use PhpParser\Parser;

class EntityFunction implements EntityInterface {

    protected static $entity_type = "function";

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
    /**
     * @var EntityClass
     */
    public $class = null;
    /**
     * @var EntityArgument[]
     */
    public $arguments = [];
    public $statics;
    public $stmts;

    /**
     * @param $name
     * @param $aliases
     * @param $line
     * @param EntityClass $class
     */
    public function __construct($name, $aliases, $line, $class = null) {
        $this->aliases = $aliases;
        $this->name = $name;
        $this->short = $name;
        $this->line = $line;
        $this->class = $class;
    }

    /**
     * @param string $body
     * @return $this
     */
    public function setBody($body) {
        $this->body = $body;
        $parser = new Parser(new Lexer);
        $this->stmts = $parser->parse('<?php'.$this->body);
        return $this;
    }


    /**
     *
     * @throws \LogicException
     * @return \ReflectionFunctionAbstract
     */
    public function scan() {
        $func        = new \ReflectionFunction($this->name);
        $this->short = $func->getShortName();
        $this->ns    = $func->getNamespaceName();
        $doc         = $func->getDocComment();
        $params      = [];

        if($doc) {
            $params = $this->_parseDocBlock($doc);
        }
        $this->_parseParams($func->getParameters(), $params);
    }

    protected function _parseDocBlock($doc) {
        $doc = preg_replace('/^\s*(\*\s*)+/mS', '', trim($doc, "*/ \t\n\r"));
        $params = [];
        if(strpos($doc, "@") !== false) {
            $doc = explode("@", $doc, 2);
            if($doc[0] = trim($doc[0])) {
                $this->description = $doc[0];
            }
            if($doc[1]) {
                foreach(preg_split('/\r?\n@/Sm', $doc[1]) as $param) {
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
                            if(preg_match('/^(.*?)\s*\$(\w+)\s*?/Sm', $param[1], $matches)) {
                                $params[ $matches[2] ] = array(
                                    "type" => $matches[1],
                                    "desc" => trim(substr($param[1], strlen($matches[0])))
                                );
                            }
                            break;
                        case 'return':
                            if(preg_match('/^(.*?)\s*$/Sm', $param[1], $matches)) {
                                $this->return["type"] = $matches[1];
                                $this->return["desc"] = isset($matches[2]) ? $matches[2] : '';
                            }
                            break;
                        default:
                            if(isset($this->options[ $param[0] ])) {
                                if(!is_array($this->options[ $param[0] ])) {
                                    $this->options[ $param[0] ] = array($this->options[ $param[0] ]);
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

        return $params;
    }

    public function _parseParams($params, $doc_params) {
        /* @var \ReflectionParameter[] $params */
        foreach($params as $param) {
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
                    $this->strict = false;
                    $argument->type = null;
                } elseif($_type === "mixed") {
                    $this->strict = false;
                    $argument->type = null;
                } else {
                    if(strpos($_type, "[]")) {
                        $this->strict = false;
                        $argument->type = 'array';
                        $argument->hint = rtrim($_type, '[]');
                    }
                    if(isset(self::$_native[$_type])) {
                        $argument->type = $_type;
                    } else {
                        $_type = ltrim($_type,'\\');
                        $argument->type = "object";
                        $argument->instance_of =  $_type;
                    }
                }
            } else {
//                $this->log(LOG_WARNING, 'Undocumented argument $'.$argument->name.' in '.$this->name.' [error.method.arg.undocumented]');
                $this->strict = false;
            }
            $this->arguments[$argument->name] = $argument;
        }
    }

    public function dump($tab = "") {
        $args = [];
        foreach($this->arguments as $arg) {
            $args[] = $arg->dump();
        }
        return static::$entity_type." {$this->name}(".($args ? implode(', ', $args) : '').'):'.($this->return ? $this->return['type'] : 'void');
    }

    public function __toString() {
        return 'function '.$this->name.'('.($this->arguments ? '...' : '').')';
    }
}