<?php

namespace Koda\Entity;


use Koda\ToolKit;

class EntityMethod extends EntityFunction {
	/**
	 * Method flags, see Koda\Entity\Flags
	 * @var int
	 */
	public $flags = 0;
    /**
     * @var EntityClass alias of $scope property
     */
    public $class;

    /**
     * @param string $name
     */
    public function __construct($name) {
	    $this->name = $name;
	    list($this->ns, $short_class, $this->short) = ToolKit::splitNames($name);
	    $this->class_name = $this->ns.'\\'.$short_class;
    }

	public function setClass($class) {
		$this->class = $class;
		return $this;
	}

    public function __toString() {
        return 'method '.$this->name;
    }

    public function dump($tab = "") {
        return parent::dump($tab)."  [".Flags::decode($this->flags)."]";
    }

	/**
	 * @param \ReflectionFunctionAbstract $reflection if null — scan itself
	 * @return $this
	 */
	public function scan(\ReflectionFunctionAbstract $reflection = null) {
        $func        = $reflection ?: new \ReflectionMethod($this->class_name, $this->short);
        if($func->isPrivate()) {
            $this->flags |= Flags::IS_PRIVATE;
        } elseif($func->isProtected()) {
            $this->flags |= Flags::IS_PROTECTED;
        } else {
            $this->flags |= Flags::IS_PUBLIC;
        }

        if($func->isStatic()) {
            $this->flags |= Flags::IS_STATIC;
        }

        if($func->isAbstract()) {
            $this->flags |= Flags::IS_ABSTRACT;
	        if($this->class) {
                $this->class->flags |= Flags::IS_ABSTRACT_IMPLICIT;
	        }
        } elseif($func->isFinal()) {
            $this->flags |= Flags::IS_FINAL;
        }

        if(isset($this->options['deprecated'])) {
            $this->flags |= Flags::IS_DEPRECATED;
        }
        parent::scan($func);
		return $this;
    }

    public function isAbstract() {
        return (bool)($this->flags & Flags::IS_ABSTRACT);
    }

    public function isFinal() {
        return (bool)($this->flags & Flags::IS_FINAL);
    }

    public function isPublic() {
        return (bool)($this->flags & Flags::IS_PUBLIC);
    }

    public function isPrivate() {
        return (bool)($this->flags & Flags::IS_PRIVATE);
    }

    public function isProtected() {
        return (bool)($this->flags & Flags::IS_PROTECTED);
    }
}