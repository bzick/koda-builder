<?php

namespace Koda\Entity;


class EntityMethod extends EntityFunction {

    protected static $entity_type = "method";

    public $flags = 0;
    /**
     * @var EntityClass alias of $scope property
     */
    public $class;

    public function __toString() {
        return 'method '.$this->name;
    }

    public function dump($tab = "") {
        return Flags::decode($this->$flags)." ".parent::dump($tab);
    }

    public function scan() {

    }
}