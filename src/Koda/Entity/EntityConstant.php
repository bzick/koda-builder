<?php
namespace Koda\Entity;


use Koda\EntityInterface;
use Koda\ToolKit;

class EntityConstant implements EntityInterface {

    public $type = 0;
    public $value;
    public $name;
    public $ns;

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
        list($this->ns, $base, $item) = ToolKit::splitNames($name);
        $this->short = $item ?: $base;
        $this->name = $name;
        if(!is_scalar($value)) {
            throw new \LogicException("Only scalar value allowed in constant");
        } else { // todo add resource handler
            $this->type = Types::detectType($value);
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