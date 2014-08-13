<?php

namespace Koda\Entity;


use Koda\ToolKit;

/**
 * Entity of the class
 * @package Koda\Entity
 */
class EntityClass  extends EntityAbstract {
    use FlagsTrait;
    /**
     * Parent class (extend keyword)
     * @var EntityClass
     */
    public $parent;
    /**
     * Parents class for interface (extend keyword)
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
     * @var
     */
    public $author;
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
     */
    public function __construct($class) {
        $this->name    = $class;
        list($this->ns, $this->short) = ToolKit::splitNames($class);
        $this->cname   = str_replace('\\', '_', $class);
        $this->escaped = addslashes($class);
//        $this->extension  = $ref->getExtension();
    }

    /**
     * @param string $name
     * @return $this
     */
    public function addAuthor($name) {
        $this->author[] = $name;
        return $this;
    }

    /**
     * @param array $options
     * @return $this
     */
    public function addOptions(array $options) {
        return $this;
    }

	/**
	 * Set class parent
	 * @param string $parent class name
	 * @param bool $multiple multiple parents enable
	 * @return $this
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
	    return $this;
    }

	/**
	 * Add interface
	 * @param string $interface interface name
	 * @return $this
	 */
    public function addInterface($interface) {
        $this->interfaces[$interface] = new \ReflectionClass($interface);
	    return $this;
    }

	/**
	 * Set aliases used in namespace
	 * @param string[] $aliases
	 * @return $this
	 */
    public function setAliases($aliases) {
        $this->aliases = $aliases;
	    return $this;
    }

    /**
     * Add constant
     * @param EntityConstant $constant
     * @return EntityConstant
     */
    public function addConstant(EntityConstant $constant) {
        return $this->constants[$constant->name] = $constant->setClass($this);
    }

    /**
     * Add property
     * @param EntityProperty $property
     * @return EntityProperty
     */
    public function addProperty(EntityProperty $property) {
        return $this->properties[$property->name] = $property->setClass($this);
    }

//    public function _addProperty($name, $line) {
//        return $this->properties[$name] = new EntityProperty($name, $line, $this);
//    }

    /**
     * Add method
     * @param EntityMethod $method
     * @return EntityMethod
     */
    public function addMethod(EntityMethod $method) {
        return $this->methods[$method->name] = $method->setClass($this);
    }
//    public function _addMethod($name, $line) {
//        $method = new EntityMethod($this->name.'::'.$name, $this->aliases, $line, $this);
//        $this->methods[$name] = $method;
//        return $method;
//    }

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
            "line: {$this->line}"
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