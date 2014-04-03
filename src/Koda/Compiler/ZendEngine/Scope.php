<?php

namespace Koda\Compiler\ZendEngine;

use Koda\Entity\EntityFunction;

class Scope {
    /**
     * @var \Koda\Entity\EntityFunction
     */
    public $function;

    public function __construct(EntityFunction $function) {
        $this->function =  $function;
    }

    public function convert() {
        dump($this->function->stmts);
    }
}