<?php

namespace Koda\Entity;


trait DefaultValueTrait {

    public $value;

    public $type = 8;

    public function setValue($value) {
        if($this instanceof EntityConstant && !is_scalar($value)) {
            throw new \LogicException("Only scalar value allowed in constant");
        }
        $this->type = Types::detectType($value);
        $this->value = $value;
        return $this;
    }

    public function getValue($escaped = false) {
        if($escaped) {
            return var_export($this->value, true);
        } else {
            return $this->value;
        }
    }
} 