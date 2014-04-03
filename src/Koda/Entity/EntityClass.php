<?php

namespace Koda\Entity;


use Koda\EntityInterface;

class EntityClass implements EntityInterface {
    /**
     * @var EntityClass
     */
    public $parent;
    /**
     * @var EntityClass[]
     */
    public $parents;
    /**
     * @var EntityClass[]
     */
    public $interfaces = [];
    /**
     * @var EntityClass[]
     */
    public $traits = [];
    /**
     * @var int
     */
    public $flags = 0;
    /**
     * @var EntityProperty[]
     */
    public $properties = [];
    /**
     * @var EntityMethod[]
     */
    public $methods = [];
    /**
     * @var EntityConstant[]
     */
    public $constants = [];
    /**
     * @var \ReflectionExtension
     */
    public $extension;
    /**
     * @var string[]
     */
    public $aliases;
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $short;
    /**
     * @var string
     */
    public $ns;
    /**
     * @var mixed
     */
    public $cname;
    /**
     * @var string
     */
    public $escaped;
    /**
     * @var
     */
    public $ref;

    public function __construct($class, $aliases, array $line) {
        $ref           = new \ReflectionClass($class);
        $this->short   = $ref->getShortName();
        $this->ns      = $ref->getNamespaceName();
        $this->aliases = $aliases;
        $this->name    = $class;
        $this->cname   = str_replace('\\', '_', $class);
        $this->escaped = addslashes($class);
        $this->line    = $line;
        $this->extension  = $ref->getExtension();
        if($ref->isInterface()) {
            $this->flags = Flags::IS_INTERFACE;
        } elseif($ref->isTrait()) {
            $this->flags = Flags::IS_TRAIT;
        } else {
            $this->flags = Flags::IS_CLASS;
        }
        if($ref->isAbstract()) {
            $this->flags |= Flags::IS_ABSTRACT;
        } elseif($ref->isFinal()) {
            $this->flags |= Flags::IS_FINAL;
        }
    }

    public function isInterface() {
        return $this->flags & Flags::IS_INTERFACE;
    }

    public function isTrait() {
        return $this->flags & Flags::IS_TRAIT;
    }

    public function isClass() {
        return $this->flags & Flags::IS_CLASS;
    }

    public function isFinal() {
        return $this->flags & Flags::IS_FINAL;
    }

    public function isAbstract() {
        return $this->flags & Flags::IS_ABSTRACT;
    }

    public function setParent($parent, $multiple = false) {
        if($multiple) {
            $this->parents[] = new \ReflectionClass($parent);
        } elseif($this->parent) {
            throw new \LogicException("Parent {$this->parent} already set for {$this}");
        } else {
            $this->parent = new \ReflectionClass($parent);
        }
    }

    public function addInterface($interface) {
        $this->interfaces[$interface] = new \ReflectionClass($interface);
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

    public function __toString() {
        return Flags::decode($this->flags & Flags::CLASS_TYPES)." {$this->name}";
    }

    public function getEscapedName() {
        return addslashes($this->__toString());
    }

    public function dump($tab = "") {
        $inf = [
            "line: {$this->line[0]}:{$this->line[1]}"
        ];
        if($this->parent) {
            $inf[] = "parent: {$this->parent->name}";
        }
        if($this->parents) {
            $inf[] = "parents: ".implode(", ", array_keys($this->parents));
        }
        if($this->interfaces) {
            $inf[] = "interfaces: ".implode(", ", array_keys($this->interfaces));
        }

        foreach(['constants', 'properties', 'methods'] as $entities) {
            if($this->$entities) {
                $inf[] = "";
            }
            foreach($this->$entities as $entity) {
                /* @var EntityInterface $entity */
                $inf[] = $entity->dump();
            }
        }
        $access = Flags::decode($this->flags & ~Flags::CLASS_TYPES);

        return Flags::decode($this->flags & Flags::CLASS_TYPES)." {$this->name} ".($access ? "[$access] " : "").
        "{\n$tab    ".implode("\n$tab    ", $inf)."\n$tab}\n";
    }

    public function quote($filter = null) {
        if($filter) {
            return '"'.call_user_func($filter, $this->escaped).'"';
        } else {
            return '"'.$this->escaped.'"';
        }
    }
}