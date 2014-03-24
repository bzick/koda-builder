<?php

namespace Koda\Sandbox;


class Primitive {

    const FIVE = 5;
    const FLOAT_FIVE = 5.5;
    const STRING_ZERO = 'five';

    public function getInt() {
        return 2;
    }

    public function getNegative() {
        return -5;
    }

    public function getString() {
        return "some string";
    }

    public function getTrue() {
        return true;
    }

    public function getFalse() {
        return false;
    }

    public function getNULL() {
        return null;
    }
}


function primitive_function() {
    return "primitive";
}