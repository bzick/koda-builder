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
    /**
     * @var int
     */
    public $cast;
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

    public function dump($tab = "") {
        if($this->type == Types::OBJECT) {
            $type = $this->instance_of;
        } else {
            $type = Types::getTypeCode($this->type);
        }
        return $type.' '.($this->is_ref ? '&' : '').'$'.$this->name.($this->is_optional ? ' = '.var_export($this->value, true) : '');
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
        $this->cast = $type;
        $this->hint = $hint;
    }

    public function allowsNull() {
        return intval($this->allows_null);
    }

	public function isOptional() {
		return intval($this->is_optional);
	}

    public function __toString() {
        return '$'.$this->name;
    }
}