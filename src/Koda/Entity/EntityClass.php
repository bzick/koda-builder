<?php

namespace Koda\Entity;


class EntityClass {
    const IS_CLASS     = 1;
    const IS_INTERFACE = 2;
    const IS_TRAIT     = 4;

    const IS_ABSTRACT = 64;
    const IS_FINAL    = 128;

    public $extends;
    public $implements = [];
    public $traits = [];

    public $flags = self::IS_CLASS;
    public $props = [];
    public $methods = [];
    public $constants = [];

    public $aliases;

    public $name;
    public $short;
    public $ns;

    public function __construct($class, array $line) {
        $this->short = $class;
        $this->name = "";
        $this->ns   = "";
        $this->line = $line;
    }

    public function setAliases($aliases) {
        $this->aliases = $aliases;
    }

    public function addConstant($name, $value) {
        $this->constants[$name] = $value;
        return $this;
    }

    public function addProperty($type, $name, $value) {

    }

    public function addMethod($name, $args, $body, $meta) {

    }

    public function setConstructor($body) {
//        $this->aliases = $aliases;
    }

    public function setDestructor($body) {
//        $this->aliases = $aliases;
    }


    public function __toString() {
        return $this->name;
    }

    public function scan() {

    }
} 