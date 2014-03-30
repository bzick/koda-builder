<?php

namespace Koda\Sandbox;

use RuntimeException as RE,
    LogicException;
use InvalidArgumentException;

const FIVE = 5;
const FLOAT_FIVE = 5.5;
const STRING_FIVE = 'five';

/**
 * @param float $x
 * @param int $y
 * @return bool
 */
function simple_function($x, $y = 5) {
    return true;
}

class Names extends \ArrayObject implements \JsonSerializable {


    const FIVE = 5;
    const FLOAT_FIVE = 5.5;
    const STRING_FIVE = 'five';

    public $five = 5;
    protected $float_five = 5.5;
    private static $string_five = 'five';

    public function __construct(Names $self, array $list = null) {

    }

    final public static function publicStatic() {

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

    public function __clone() {

    }

    public function __destruct() {

    }

    public function jsonSerialize() {

    }
}
