<?php
/**
 * @author Ivan Shalganov <bzick@megagroup.ru>
 * @created 26.03.14 21:13
 * @copyright megagroup.ru, 2014
 */

namespace Koda\Entity;


use Koda\EntityInterface;

class EntityProperty implements EntityInterface {

    /**
     * @var EntityClass
     */
    public $class;
    /**
     * @var string
     */
    public $name;
    public $type = Flags::IS_PUBLIC;

    public function dump($tab = "") {
        return Flags::keyword($this->type).' '.$this->class->name.'::$'.$this->name;
    }

    public function __toString() {
        return $this->class->name.'::$'.$this->name;
    }
}