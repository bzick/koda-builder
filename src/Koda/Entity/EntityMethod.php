<?php

namespace Koda\Entity;


use Koda\ToolKit;

class EntityMethod extends EntityFunction {
    use FlagsTrait;
    /**
     * @param string $name
     */
    public function __construct($name) {
	    $this->name = $name;
	    list($this->ns, $short_class, $this->short) = ToolKit::splitNames($name);
	    $this->class_name = $this->ns.'\\'.$short_class;
    }

    public function __toString() {
        return 'method '.$this->name.'( ... )';
    }

    public function dump($tab = "") {
        return parent::dump($tab)."  [".Flags::decode($this->flags)."]";
    }

    public function getReflection() {
        return new \ReflectionMethod($this->class->name, $this->short);
    }
}