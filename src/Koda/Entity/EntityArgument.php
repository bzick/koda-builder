<?php

namespace Koda\Entity;

use Koda\EntityInterface;

class EntityArgument implements EntityInterface {

    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $description;
    public $is_ref = false;
    /**
     * @var bool
     */
    public $is_optional;
    public $default_value;
    public $position;
    public $type;
    public $instance_of;
    public $hint;

    public function __construct(EntityFunction $function, $name) {
        $this->function = $function;
        $this->name = $name;
    }

    public function isRef() {
        return intval($this->is_ref);
    }

    public function dump($tab = "") {
        return ($this->type?:'mixed').' $'.$this->name.($this->is_optional ? ' = '.var_export($this->default_value, true) : '');
    }

    public function __toString() {
        return '$'.$this->name;
    }
}