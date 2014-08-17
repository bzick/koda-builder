<?php

namespace Koda\Entity;


use Koda\Tokenizer;
use Koda\ToolKit;
use PhpParser\Lexer;
use PhpParser\Parser;

class EntityFunction extends EntityAbstract {
    use ClassTrait;

    protected static $entity_type = "function";

    /**
     * @var Tokenizer
     */
    public $tokens;
    public $strict = true;
    public $return_type = -1;
    public $return_desc = "";
    public $return_ref = 0;
    public $options;
    public $is_ref    = false;
    public $generator = false;
    public $required  = 0;
    public $short;
    public $ns;
    public $aliases;
    public $body;
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
            $this->tokens = new Tokenizer($body);
//            $parser = new Parser(new Lexer);
//            $this->stmts = $parser->parse('<?php '.$this->body);
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
     * Push new argument to the end of list of arguments
     * @param EntityArgument $argument
     */
    public function pushArgument(EntityArgument $argument) {
        $this->arguments[] = $argument;
        $argument->setFunction($this);
        if(!$argument->isOptional()) {
            $this->required++;
        }
        if(Types::$complex[$argument->type]) {
            $this->info("disable strict mode because of argument $argument is complex (type ".Types::getTypeCode($argument->type).")");
            $this->strict = false;
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

    public function getReflection() {
        return new \ReflectionFunction($this->name);
    }
}