<?php
namespace Koda\Entity;


use Koda\EntityInterface;
use Koda\ToolKit;

/**
 * Entity of the constant
 * @package Koda\Entity
 */
class EntityConstant implements EntityInterface {

    /**
     * Value type
     * @see Koda\Entity\Types
     * @var int
     */
    public $type = 0;
    /**
     * Value
     * @var bool|float|int|string
     */
    public $value;
    /**
     * Name with namespace
     * @var string
     */
    public $name;
    /**
     * Short name (without namespace)
     * @var
     */
    public $short;
    /**
     * Namespace name
     * @var string
     */
    public $ns;

    /**
     * Line position
     * @var array
     */
    public $line;
    /**
     * Class-owner of the constant. If null constant is global
     * @var EntityClass|null
     */
    public $class;

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

    /**
     * @inheritdoc
     */
    public function dump($tab = "") {
        return "const {$this->name} = ".var_export($this->value, true);
    }

    /**
     * @inheritdoc
     */
    public function __toString() {
        return "const {$this->name}";
    }
}