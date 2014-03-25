<?php

namespace Koda\Entity;


class EntityMethod extends EntityFunction {

    public $flags = 0;
    public $class;

    public function setClass(EntityClass $class, $flags) {
        $this->class = $class;
        $this->flags = $flags;
    }
}