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
        foreach($this->function->stmts as $stmt) {
//            drop(get_class($stmt));
        }
//        drop($this->function->stmts);
    }

    public function stmtReturn($value) {
        if($value->is_const) {
            switch($value->type) {

            }
        } else {
            return "RETURN_ZVAL($value, 1);";
        }
    }
}