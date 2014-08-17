<?php

namespace Koda\Entity;


trait ClassTrait {

    /**
     * @var EntityClass
     */
    public $class;

    /**
     * @param EntityClass $class
     * @return $this
     */
    public function setClass(EntityClass $class) {
        $this->class = $class;
        return $this;
    }
} 