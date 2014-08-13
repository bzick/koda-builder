<?php

namespace Koda\Entity;

class EntityProperty extends EntityAbstract {
    use DefaultValueTrait;
    use ClassTrait;
    use FlagsTrait;

    public function __construct($name) {
        $this->name = $name;
    }

    public function dump($tab = "") {
        return 'prop '.$this->class->name.'::$'.$this->name.' = '.var_export($this->value, true).' ['.Flags::decode($this->flags).']';
    }

    public function __toString() {
        return $this->class->name.'::$'.$this->name;
    }
}