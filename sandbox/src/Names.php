<?php

namespace Koda\Sandbox;

use RuntimeException as RE,
    LogicException;
use InvalidArgumentException;

const FIVE = 5;
const FLOAT_FIVE = 5.5;
const STRING_FIVE = 'five';

function simple_function($x, $y = 5) {
    return true;
}

class Names {

    public static function publicStatic() {

    }

    private static function privateStatic() {

    }

    protected static function protectedStatic() {

    }

    public function publicMethod() {

    }

    private function privateMethod() {

    }

    protected function protectedMethod() {

    }

}
