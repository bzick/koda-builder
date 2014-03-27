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

    /**
     * @param string $name method name (without class name)
     * @param array $line
     * @return EntityMethod
     */
    public function addMethod($name, $line) {
        $method = new EntityMethod($name, $this->aliases, $line, $this);
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
            $method[] = $method->dump($tab.'    ');
        }
        $properties = [];
        foreach($this->properties as $property) {
            $properties[] = $property->dump($tab.'    ');
        }
        var_dump($constants, $properties, $functions);
        return "class {$this->name} {".
        "\n$tab    ".implode("\n$tab    ", $constants)."\n".
        "\n$tab    ".implode("\n$tab    ", $properties)."\n".
        "\n$tab    ".implode("\n$tab    ", $functions)."\n".
        "\n{$tab}}";
    }
}