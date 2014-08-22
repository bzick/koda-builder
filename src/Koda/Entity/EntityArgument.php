<?php

namespace Koda\Entity;


class EntityArgument extends EntityAbstract {
    use DefaultValueTrait;
    public $is_ref = false;
    /**
     * @var bool
     */
    public $is_optional;
    public $position;
    public $instance_of;
    public $allows_null;
    public $is_complex = false;
    /**
     * @var int
     */
    public $cast = 0;
    /**
     * @var string
     */
    public $hint;
    /**
     * @var EntityFunction
     */
    public $function;

    public function __construct($name) {
        $this->name = $name;
    }

    public function setFunction(EntityFunction $function) {
        $this->function = $function;
    }

    public function isRef() {
        return intval($this->is_ref);
    }

    /**
     * @param string $tab
     * @return string
     */
    public function dump($tab = "") {
        if($this->cast == Types::OBJECT) {
            $cast = $this->instance_of;
        } else {
            $cast = Types::getTypeCode($this->cast);
        }
        return $cast.' '.($this->is_ref ? '&' : '').'$'.$this->name.($this->is_optional ? ' = '.var_export($this->value, true) : '');
    }

    /**
     * @param bool $is_optional
     * @return $this
     */
    public function setOptional($is_optional) {
        $this->is_optional = $is_optional;
        return $this;
    }

    /**
     * @param bool $allows_null
     * @return $this
     */
    public function setNullAllowed($allows_null) {
        $this->allows_null = $allows_null;
        return $this;
    }

    /**
     * @param bool $by_ref
     * @return $this
     */
    public function setByRef($by_ref) {
        $this->is_ref = $by_ref;
        return $this;
    }

    /**
     * @param int $type
     * @param string $hint
     */
    public function setCast($type, $hint = null) {
        $this->is_complex = isset(Types::$complex[$type]);
        $this->cast = $type;
        if($type == Types::OBJECT) {
            $this->instance_of = $hint;
        } elseif($type == Types::ARR) {
            $this->hint = $hint;
        }
    }

    /**
     * @return int
     */
    public function allowsNull() {
        return intval($this->allows_null);
    }

    /**
     * @return int
     */
    public function isOptional() {
		return intval($this->is_optional);
	}

    public function __toString() {
        return '$'.$this->name;
    }
}