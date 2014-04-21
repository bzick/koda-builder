<?php

namespace Koda;

use Koda\Entity\EntityFunction;
use Koda\Entity\Types;
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

	/**
	 * @param string $doc
	 * @return array
	 */
	public static function parseDoc($doc) {
		$doc = preg_replace('/^\s*(\*\s*)+/mS', '', trim($doc, "*/ \t\n\r"));
		$info = [
			"desc" => "",
			"return" => [
				'type' => -1,
				'desc' => ''
			],
			"options" => [],
		];
		$params = [];
		if(strpos($doc, "@") !== false) {
			$doc = explode("@", $doc, 2);
			if($doc[0] = trim($doc[0])) {
				$info["desc"] = $doc[0];
			}
			if($doc[1]) {
				foreach(preg_split('/\r?\n@/Sm', $doc[1]) as $param) {
					$param = preg_split('/\s+/', $param, 2);
					if(!isset($param[1])) {
						$param[1] = "";
					}
					switch(strtolower($param[0])) {
						case 'desc':
						case 'description':
							if(empty($info["desc"])) {
								$info["desc"] = $param[1];
							}
							break;
						case 'param':
							if(preg_match('/^(.*?)\s*\$(\w+)\s*?/Sm', $param[1], $matches)) {
								$params[ $matches[2] ] = array(
									"type" => $matches[1],
									"desc" => trim(substr($param[1], strlen($matches[0])))
								);
							}
							break;
						case 'return':
							if(preg_match('/^(.*?)\s*$/Sm', $param[1], $matches)) {
								$info["return"]["type"] = Types::getType($matches[1]);
								$info["return"]["desc"] = isset($matches[2]) ? $matches[2] : '';
							}
							break;
						default:
							if(isset($info["options"][ $param[0] ])) {
								if(!is_array($params["options"][ $param[0] ])) {
									$info["options"][ $param[0] ] = array($info["options"][ $param[0] ]);
								}
								$info["options"][ $param[0] ][] = $param[1];
							} else {
								$info["options"][ $param[0] ] = $param[1];
							}
					}
				}
			}
		} else {
			$info["desc"] = $doc;
		}
		$info['params'] = $params;
		return $info;
	}
}

