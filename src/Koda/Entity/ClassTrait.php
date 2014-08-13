<?php
/**
 * Created by PhpStorm.
 * User: bzick
 * Date: 11.08.14
 * Time: 10:03
 */

namespace Koda\Entity;


trait ClassTrait {

    public $class;

    public function setClass(EntityClass $class) {
        $this->class = $class;
        return $this;
    }
} 