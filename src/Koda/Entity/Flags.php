<?php

namespace Koda\Entity;


class Flags {

    const IS_CLASS     = 0x1;
    const IS_INTERFACE = 0x2;
    const IS_TRAIT     = 0x4;

    const CLASS_TYPES  = 0xF;

    /**
     * Flag denotes that class or method was explicitly defined as abstract by using the keyword.
     */
    const IS_ABSTRACT = 0x10;
    const IS_FINAL    = 0x20;
    /**
     * Flag is used for abstract classes (since it is set by any abstract method even interfaces MAY have it set, too).
     */
    const IS_ABSTRACT_IMPLICIT = 0x40;
    const MODIFIERS    = 0xF0;

    const IS_STATIC      = 0x100;
    const IS_PUBLIC      = 0x200;
    const IS_PRIVATE     = 0x400;
    const IS_PROTECTED   = 0x800;
    const ACCESS_TYPE    = 0xF00;

    const IS_DEPRECATED  = 0x1000;

    public static $keywords = [
        'abstract'   => self::IS_ABSTRACT,
        'final'      => self::IS_FINAL,
        'static'     => self::IS_STATIC,
        'deprecated' => self::IS_DEPRECATED,

        'public'    => self::IS_PUBLIC,
        'private'   => self::IS_PRIVATE,
        'protected' => self::IS_PROTECTED,

        'class'     => self::IS_CLASS,
        'interface' => self::IS_INTERFACE,
        'trait'     => self::IS_TRAIT,
    ];

    public static $flags = [
        self::IS_ABSTRACT   => 'abstract',
        self::IS_FINAL      => 'final',
        self::IS_STATIC     => 'static',
        self::IS_DEPRECATED => 'deprecated',

        self::IS_PUBLIC     => 'public',
        self::IS_PRIVATE    => 'private',
        self::IS_PROTECTED  => 'protected',

        self::IS_CLASS      => 'class',
        self::IS_INTERFACE  => 'interface',
        self::IS_TRAIT      => 'trait',
    ];

    public static function decode($flags) {
        $keys = [];

        foreach(self::$flags as $flag => $key) {
            if($flags & $flag) {
                $keys[] = $key;
            }
        }

        return implode(" ", $keys);
    }

    public static function keyword($flag) {
        if($flag && isset(self::$flags[$flag])) {
            return self::$flags[$flag];
        } else {
            return false;
        }

    }
}