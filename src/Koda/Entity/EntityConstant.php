<?php
namespace Koda\Entity;


class EntityConstant {

    public $type = 0;
    public $value;
    public $name;
    /**
     * @var EntityFile
     */
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
}