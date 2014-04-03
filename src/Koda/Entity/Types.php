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
        "bool" => self::BOOLEAN,
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

    public static $types = [
        self::MIXED => "mixed",
        self::BOOLEAN => "boolean",
        self::INT => "int",
        self::DOUBLE => "double",
        self::STRING => "string",
        self::ARR => "array",
        self::OBJECT => "object",
        self::RESOURCE => "resource",
        self::NIL => "null",
        self::CALLBACK => "callable",
    ];

    public static function detectType($value) {
        return self::getType(gettype($value));
    }

    public static function getType($code) {
        if(isset(self::$codes[$code])) {
            return self::$codes[$code];
        } else {
            throw new \LogicException("Unknown code type '$code'");
        }
    }

    public static function getTypeCode($type) {
        if(isset(self::$types[$type])) {
            return self::$types[$type];
        } else {
            throw new \LogicException("Unknown type");
        }
    }
}