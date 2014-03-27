<?php
namespace Koda\Entity;


use Koda\EntityInterface;

class EntityConstant implements EntityInterface {

    public $type = 0;
    public $value;
    public $name;
    public $line;

    public function __construct($name, $value, $line) {
        $this->name = $name;
        if(!is_scalar($value)) {
            throw new \LogicException("Only scalar value allowed in constant");
        } else { // todo add resource handler
            $this->type = Types::getType($value);
        }
        $this->value = $value;
        $this->line = $line;
    }

    public function dump($tab = "") {
        return "const {$this->name} = {$this->value}";
    }

    public function __toString() {
        return "const {$this->name}";
    }
}