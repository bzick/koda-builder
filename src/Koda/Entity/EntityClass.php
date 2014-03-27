<?php

namespace Koda\Entity;


use Koda\EntityInterface;

class EntityClass implements EntityInterface {
    public $extends;
    public $implements = [];
    public $traits = [];

    public $flags = 0;
    /**
     * @var EntityProperty[]
     */
    public $properties = [];
    /**
     * @var EntityFunction[]
     */
    public $methods = [];
    /**
     * @var EntityConstant[]
     */
    public $constants = [];

    public $aliases;

    public $name;
    public $short;
    public $ns;
    public $ref;

    public function __construct($class, $aliases, array $line) {
        $ref           = new \ReflectionClass($class);
        $this->short   = $ref->getShortName();
        $this->ns      = $ref->getNamespaceName();
        $this->aliases = $aliases;
        $this->name    = $class;
        $this->line    = $line;
    }

    public function setAliases($aliases) {
        $this->aliases = $aliases;
    }

    public function addConstant($name, $line) {
        return $this->constants[$name] = new EntityConstant($this->name.'::'.$name, constant($this->name.'::'.$name), $line, $this);
    }

    public function addProperty($name, $line) {
        return $this->properties[$name] = new EntityProperty($name, $line, $this);
    }

    /**
     * @param string $name method name (without class name)
     * @param array $line
     * @return EntityMethod
     */
    public function addMethod($name, $line) {
        $method = new EntityMethod($this->name.'::'.$name, $this->aliases, $line, $this);
        $this->methods[$name] = $method;
        return $method;
    }

    public function setConstructor($body) {
//        $this->aliases = $aliases;
    }

    public function setDestructor($body) {
//        $this->aliases = $aliases;
    }


    public function __toString() {
        return "class {$this->name}";
    }

    public function scan() {

    }

    public function dump($tab = "") {
        $constants = [];
        foreach($this->constants as $const) {
            $constants[] = $const->dump($tab.'    ');
        }
        $functions = [];
        foreach($this->methods as $method) {
            $functions[] = $method->dump($tab.'    ');
        }
        $properties = [];
        foreach($this->properties as $property) {
            $properties[] = $property->dump($tab.'    ');
        }
        return "class {$this->name} {".
        ($constants  ? "\n$tab    ".implode("\n$tab    ", $constants)."\n" : "").
        ($properties ? "\n$tab    ".implode("\n$tab    ", $properties)."\n": "").
        ($functions  ? "\n$tab    ".implode("\n$tab    ", $functions)."\n" : "").
        "\n{$tab}}";
    }
}