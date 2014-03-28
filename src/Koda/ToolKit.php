<?php

namespace Koda;

class ToolKit {

    public static $tab = "    ";

    public static function dump($item, $tab = "") {
        switch(gettype($item)) {
            case "integer":
            case "NULL":
            case "double":
            case "string":
            case "boolean":
            case "resource":
                return var_export($item, true);
            case "array":
                foreach($item as $k => &$v) {
                    $v = $tab.self::$tab."[$k] => ".self::dump($v, $tab.self::$tab);
                }
                return "array(".count($item).") {\n$tab".implode("\n$tab", $item)."\n$tab}";
            case "object":
                if($item instanceof EntityInterface) {
                    return $item->dump($tab);
                } elseif(method_exists($item, '__toString')) {
                    return $item->__toString();
                } else {
                    return "object(".get_class($item).")";
                }
            default:
                return "unknown type ".gettype($item);
        }
    }

    public static function splitNames($name) {
        $ns = $basename = $item = null;
        if(strpos($name, '::')) {
            list($ns, $item) = explode('::', $name, 2);
        } else {
            $ns = $name;
        }
        $ns = trim($ns, '\\');
        if(strpos($ns, '\\')) {
            $ns = explode('\\', $ns);
            $basename = array_pop($ns);
            $ns = implode('\\', $ns);
        } else {
            $basename = $ns;
            $ns = null;
        }

        return [$ns, $basename, $item];
    }
}

