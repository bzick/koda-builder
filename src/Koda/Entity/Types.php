<?php

namespace Koda\Entity;


class Types {

    const MIXED    = 0;
    const BOOLEAN  = 1;
    const INT      = 2;
    const DOUBLE   = 3;
    const STRING   = 4;
    const ARR      = 5;
    const OBJECT   = 6;
    const RESOURCE = 7;
    const NIL      = 8;
    const CALLBACK = 9;

    public static $codes = [
        "mixed" => self::MIXED,
        "boolean" => self::BOOLEAN,
        "int" => self::INT,
        "integer" => self::INT,
        "double" => self::DOUBLE,
        "float" => self::DOUBLE,
        "string" => self::STRING,
        "array" => self::ARR,
        "object" => self::OBJECT,
        "resource" => self::RESOURCE,
        "null" => self::NIL,
        "callable" => self::CALLBACK,
        "callback" => self::CALLBACK,
    ];

    public static function getType($value) {
        if(isset(self::$codes[gettype($value)])) {
            return self::$codes[gettype($value)];
        } else {
            throw new \LogicException("Unknown value type");
        }
    }
}