<?php

namespace Koda\Entity;


use Koda\EntityInterface;

/**
 * Entity of the class
 * @package Koda\Entity
 */
class EntityClass implements EntityInterface {
    /**
     * Parent class (extend keyword)
     * @var EntityClass
     */
    public $parent;
    /**
     * Parent class for interface (extend keyword)
     * @var EntityClass[]
     */
    public $parents;
    /**
     * Interfaces
     * @var EntityClass[]
     */
    public $interfaces = [];
    /**
     * Traits
     * @var EntityClass[]
     */
    public $traits = [];
    /**
     * Class flags
     * @var int
     */
    public $flags = 0;
    /**
     * List of properties
     * @var EntityProperty[]
     */
    public $properties = [];
    /**
     * List of methods
     * @var EntityMethod[]
     */
    public $methods = [];
    /**
     * List of constants
     * @var EntityConstant[]
     */
    public $constants = [];
    /**
     * Extension which defined the class. Property NULL if class defined in the project
     * @var \ReflectionExtension
     */
    public $extension;
    /**
     * List of aliases used in namespace (use keyword in namespace)
     * @var string[]
     */
    public $aliases;
    /**
     * Full name of the class (with namespace)
     * @var string
     */
    public $name;
    /**
     * Short name of the class (without namespace)
     * @var string
     */
    public $short;
    /**
     * Name of namespace
     * @var string
     */
    public $ns;
    /**
     * Name of the class for C lang
     * @var mixed
     */
    public $cname;
    /**
     * Escaped full name of the class
     * @var string
     */
    public $escaped;

    /**
     * @param string $class
     * @param string[] $aliases
     * @param array $line
     */
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

    /**
     * Checks if the class is an interface
     * @return int
     */
    public function isInterface() {
        return $this->flags & Flags::IS_INTERFACE;
    }

    /**
     * Checks if the class is an trait
     * @return int
     */
    public function isTrait() {
        return $this->flags & Flags::IS_TRAIT;
    }

    /**
     * Checks if the class is an plain class
     * @return int
     */
    public function isClass() {
        return $this->flags & Flags::IS_CLASS;
    }

    /**
     * Checks if the class is an final class
     * @return int
     */
    public function isFinal() {
        return $this->flags & Flags::IS_FINAL;
    }

    /**
     * Checks if the class is an abstract class or interface
     * @return int
     */
    public function isAbstract() {
        return $this->flags & Flags::IS_ABSTRACT;
    }

    /**
     * Set class parent
     * @param string $parent class name
     * @param bool $multiple multiple parents enable
     * @throws \LogicException
     */
    public function setParent($parent, $multiple = false) {
        if($multiple) {
            $this->parents[$parent] = new \ReflectionClass($parent);
        } elseif($this->parent) {
            throw new \LogicException("Parent {$this->parent} already set for {$this}");
        } else {
            $this->parent = new \ReflectionClass($parent);
        }
    }

    /**
     * Add interface
     * @param string $interface interface name
     */
    public function addInterface($interface) {
        $this->interfaces[$interface] = new \ReflectionClass($interface);
    }

    /**
     * Set aliases used in namespace
     * @param $aliases
     */
    public function setAliases($aliases) {
        $this->aliases = $aliases;
    }

    /**
     * Add constant
     * @param string $name
     * @param array $line
     * @return EntityConstant
     */
    public function addConstant($name, $line) {
        return $this->constants[$name] = new EntityConstant($this->name.'::'.$name, constant($this->name.'::'.$name), $line, $this);
    }
    /**
     * Add property
     * @param string $name
     * @param array $line
     * @return EntityProperty
     */
    public function addProperty($name, $line) {
        return $this->properties[$name] = new EntityProperty($name, $line, $this);
    }

    /**
     * Add method
     * @param string $name method name (without class name)
     * @param array $line
     * @return EntityMethod
     */
    public function addMethod($name, $line) {
        $method = new EntityMethod($this->name.'::'.$name, $this->aliases, $line, $this);
        $this->methods[$name] = $method;
        return $method;
    }

    /**
     * @inheritdoc
     */
    public function __toString() {
        return Flags::decode($this->flags & Flags::CLASS_TYPES)." {$this->name}";
    }

    /**
     * Return escaped __toString() result
     * @return string
     */
    public function getEscapedName() {
        return addslashes($this->__toString());
    }

    /**
     * @inheritdoc
     */
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

    /**
     * Return escaped and quoted string
     * @param callable $filter
     * @return string
     */
    public function quote(callable $filter = null) {
        if($filter) {
            return '"'.call_user_func($filter, $this->escaped).'"';
        } else {
            return '"'.$this->escaped.'"';
        }
    }
}