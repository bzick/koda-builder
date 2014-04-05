<?php

namespace Koda;

use Koda\Entity\EntityFunction;
use PhpParser\Node;
use PhpParser\NodeDumper;

class ToolKit {

    public static $tab = "    ";

    /**
     * @param $item
     * @param string $tab
     * @return mixed|string
     */
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
                } elseif($item instanceof EntityFunction) {
                    $nodeDumper = new NodeDumper;
                    return $item->dump($tab)."{\n".$nodeDumper->dump($item->stmts)."\n}";
                } elseif($item instanceof Node) {
                    $nodeDumper = new NodeDumper;
                    return $nodeDumper->dump($item);
                } elseif(method_exists($item, '__toString')) {
                    return $item->__toString();
                } else {
                    return "object(".get_class($item).")";
                }
            default:
                return "unknown type ".gettype($item);
        }
    }

    /**
     * Split entity name
     * @param string $name
     * @return array [0 => namespace, 1 => short name, 2 => name after '::']: Some\NS\myClass::isMethod => [Some\NS, myClass, isMethod]
     */
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

