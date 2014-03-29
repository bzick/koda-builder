<?php

namespace Koda\Entity;


class EntityMethod extends EntityFunction {

    protected static $entity_type = "method";

    public $flags = 0;
    /**
     * @var EntityClass alias of $scope property
     */
    public $class;

    /**
     * @param $name
     * @param $aliases
     * @param $line
     * @param EntityClass $class
     */
    public function __construct($name, $aliases, $line, $class = null) {
        list($class_name, $short) = explode("::", $name, 2);
        $this->aliases = $aliases;
        $this->name = $name;
        $this->short = $short;
        $this->line = $line;
        $this->class = $class;
    }

    public function __toString() {
        return 'method '.$this->name;
    }

    public function dump($tab = "") {
        return parent::dump($tab)."  [".Flags::decode($this->flags)."]";
    }

    public function scan() {
        $func        = new \ReflectionMethod($this->class->name, $this->short);
        $this->short = $func->getShortName();
        $this->ns    = $func->getNamespaceName();
        $doc         = $func->getDocComment();
        $params      = [];

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
        } elseif($func->isFinal()) {
            $this->flags |= Flags::IS_FINAL;
        }

        if($doc) {
            $params = $this->_parseDocBlock($doc);
        }
        if(isset($this->options['deprecated'])) {
            $this->flags |= Flags::IS_DEPRECATED;
        }
        $this->_parseParams($func->getParameters(), $params);
    }
}