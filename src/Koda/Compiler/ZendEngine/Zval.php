<?php

namespace Koda\Compiler\ZendEngine;


use Koda\Entity\Types;

class Zval {

	public $type = 0;
	public $value;

	public $is_const = true;
	public $is_var = false;
	public $is_mixed = false;
	public $name;
	public $cname;
	public $is_tmp = true;
	public $code;

//	public static function scalar($value) {
//		$val = new self();
//		$val->type = Types::detectType($value);
//		$val->value = $value;
//		$val->setScalar();
//		return $val;
//	}

	public function __construct(Scope $scope) {
		$this->scope = $scope;
	}

	public function setScalar($value) {
		$this->type = Types::detectType($value);
		$this->value = $value;
		$this->is_const = true;
		$this->is_var = false;
		$this->is_mixed = false;
		return $this;
	}

	public function setDynamic() {

	}

	public function toInt() {

	}

	public function toNumber() {
		if($this->type != Types::INT && $this->type != Types::DOUBLE) {
			if($this->is_const) {
                $this->value *= 1;
			} else {
                $this->code[] = "convert_to_number({$this->cname});";
            }
		}
	}

	public function toString() {
		if($this->type != Types::STRING) {
			if($this->is_const) {
				$this->value = strval($this->type);
			} else {
                $this->code[] = "convert_to_string({$this->cname});";
            }
		}
	}

	private function _initZval() {
		switch($this->type) {

		}
	}

	public function isScalar() {
		return $this->is_const;
	}
}