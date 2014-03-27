<?php
namespace Koda\Entity;


use Koda\EntityInterface;

class EntityConstant implements EntityInterface {

    public $type = 0;
    public $value;
    public $name;
    public $line;
    public $class;
    public $short;

    /**
     * @param string $name
     * @param mixed $value
     * @param array $line
     * @param EntityClass $class
     * @throws \LogicException
     */
    public function __construct($name, $value, $line, $class = null) {
        $this->name = $name;
        if($class) {
            $this->short = explode('\\', $name, 2)[1];
        } else {
            $this->short = trim('\\', strrchr($name, '\\'));
        }
        if(!is_scalar($value)) {
            throw new \LogicException("Only scalar value allowed in constant");
        } else { // todo add resource handler
            $this->type = Types::getType($value);
        }
        $this->value = $value;
        $this->line = $line;
        $this->class = $class;
    }

    public function dump($tab = "") {
        return "const {$this->name} = ".var_export($this->value, true);
    }

    public function __toString() {
        return "const {$this->name}";
    }
}