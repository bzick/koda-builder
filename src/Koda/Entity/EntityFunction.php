<?php

namespace Koda\Entity;


use Koda\EntityInterface;
use Koda\ToolKit;
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
    public $description = "";
    public $return_type = -1;
    public $return_desc = "";
    public $return_ref = 0;
    public $options;
    public $is_ref    = false;
    public $generator = false;
    public $required  = 0;
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
    public $stmts = [];

    /**
     * @param string $name
     */
    public function __construct($name) {
        $this->name = $name;
	    list($this->ns, $this->short) = ToolKit::splitNames($name);
    }

	/**
	 * @param array $aliases
	 * @return $this
	 */
	public function setAliases(array $aliases) {
		$this->aliases = $aliases;
		return $this;
	}

	/**
	 * @param $line
	 * @return $this
	 */
	public function setLine($line) {
		$this->line = $line;
		return $this;
	}

	/**
	 * @param string $desc
	 * @return $this
	 */
	public function setDescription($desc) {
		$this->description = $desc;
		return $this;
	}

	/**
	 * Set information about return value
	 * @param int $type one of Type::* constant
	 * @param int $is_ref
	 * @param string $desc
	 * @return $this
	 */
	public function setReturnInfo($type, $is_ref = 0, $desc = "") {
		$this->return_type = $type;
		$this->return_desc = $desc;
		$this->return_ref  = intval($is_ref);
		return $this;
	}

	public function setOptions($options) {
		$this->options = $options;
		return $this;
	}

    /**
     * @param string $body
     * @return $this
     */
    public function setBody($body) {
        $body = trim($body);
        if($body) {
            $this->body = $body;
            $parser = new Parser(new Lexer);
            $this->stmts = $parser->parse('<?php '.$this->body);
        }
        return $this;
    }

	/**
	 * @return int
	 */
	public function isReturnRef() {
        return $this->return_ref;
    }

	/**
	 *
	 * @param \ReflectionFunctionAbstract $reflection
	 * @return $this
	 */
    public function scan(\ReflectionFunctionAbstract $reflection = null) {
        $func        = $reflection ?: new \ReflectionFunction($this->name);
        $doc         = $func->getDocComment();
        $params      = [];

        if($doc) {
	        $info = ToolKit::parseDoc($doc);
	        $this->setDescription($info['desc']);
	        $this->setReturnInfo($info['return']['type'], $func->returnsReference(), $info['return']['desc']);
	        $this->setOptions($info['options']);
	        $params = $info["params"];
        }
        $this->_parseParams($func->getParameters(), $params);
	    return $this;
    }

	/**
	 * Parse parameters
	 * @param \ReflectionParameter[] $params
	 * @param array $doc_params
	 */
	private function _parseParams($params, $doc_params) {
        /* @var \ReflectionParameter[] $params */
        foreach($params as $param) {
            $argument = new EntityArgument($this, $param->name);
            if(isset($doc_params[ $param->name ]["desc"])) {
                $argument->description = $doc_params[ $param->name ]["desc"];
            }
            $argument->is_optional = $param->isOptional();
            $argument->allows_null = $param->allowsNull();
            if(!$argument->is_optional) {
                $this->required++;
            }
            $argument->default_value = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null;
            $argument->position = $param->getPosition();
            $argument->is_ref = $param->isPassedByReference();
            /** @var \ReflectionParameter $param */
            if($param->isArray()) {
                $this->strict = false;
                $argument->type = Types::ARR;
            }
            if($c = $param->getClass()) {
                $this->strict = false;
                $argument->type = Types::OBJECT;
                $argument->instance_of = $c->name;
            } elseif(isset($doc_params[ $param->name ])) {
                $_type = $doc_params[ $param->name ]["type"];
                if(strpos($_type, "|")) { // multiple types
                    $this->strict = false;
                    $argument->type = Types::MIXED;
                } elseif($_type === "mixed") {
                    $this->strict = false;
                    $argument->type = Types::MIXED;
                } else {
                    if(strpos($_type, "[]")) {
                        $this->strict = false;
                        $argument->type = Types::ARR;
                        $argument->hint = rtrim($_type, '[]');
                    }
                    if(isset(self::$_native[$_type])) {
                        $argument->type = Types::getType($_type);
                    } else {
                        $_type = ltrim($_type,'\\');
                        $argument->type = Types::OBJECT;
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
        return static::$entity_type." {$this->name}(".($args ? implode(', ', $args) : '').'):'.($this->return_type == -1 ? 'void' : Types::getTypeCode($this->return_type));
    }

    public function __toString() {
        return 'function '.$this->name.'('.($this->arguments ? '...' : '').')';
    }
}