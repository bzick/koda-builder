<?php
/**
 * @author Ivan Shalganov <bzick@megagroup.ru>
 * @created 26.03.14 21:13
 * @copyright megagroup.ru, 2014
 */

namespace Koda\Entity;


use Koda\EntityInterface;

class EntityProperty implements EntityInterface {

    /**
     * @var EntityClass
     */
    public $class;
    /**
     * @var string
     */
    public $name;
    public $value;
    public $type = Types::NIL;
    public $flags = 0;

    public function __construct($name, $line, $class) {
        $this->class = $class;
        $this->name = $name;
        $property = new \ReflectionProperty($this->class->name, $name);
        if($property->isPrivate()) {
            $this->flags |= Flags::IS_PRIVATE;
        } elseif($property->isProtected()) {
            $this->flags |= Flags::IS_PROTECTED;
        } else {
            $this->flags |= Flags::IS_PUBLIC;
        }

        if($property->isStatic()) {
            $this->flags |= Flags::IS_STATIC;
        }

        if($property->isDefault()) {
            $this->value = $property->getDeclaringClass()->getDefaultProperties()[$name];
            $this->type  = Types::getType($this->value);
        } else {
            $this->type  = Types::NIL;
        }
    }

    public function dump($tab = "") {
        return 'prop '.$this->class->name.'::$'.$this->name.'  ['.Flags::decode($this->flags).']';
    }

    public function __toString() {
        return $this->class->name.'::$'.$this->name;
    }
}