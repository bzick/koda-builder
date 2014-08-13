<?php
namespace Koda\Entity;

use Koda\ToolKit;

/**
 * Entity of the constant
 * @package Koda\Entity
 */
class EntityConstant extends EntityAbstract {
    use DefaultValueTrait;
    use ClassTrait;
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
     * @param string $name
     */
    public function __construct($name) {
        list($this->ns, $base, $item) = ToolKit::splitNames($name);
        $this->short = $item ?: $base;
        $this->name = $name;

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