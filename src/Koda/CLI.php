<?php

namespace Koda;


use Koda\Entity\EntityArgument;
use Koda\Entity\EntityClass;
use Koda\Entity\EntityMethod;
use Koda\Entity\Types;

class CLI {

    private static $_reserved = [
        'true'  => true,
        'yes'   => true,
        'y'     => true,
        'false' => false,
        'no'    => false,
        'n'     => false,
        'null'  => null,
    ];
    /**
     * @param object $object
     * @return bool
     * @throws \InvalidArgumentException
     */
	public static function configure($object) {
		$options = self::_parse();
		$class = new \ReflectionClass(get_class($object));
		$ce    = new EntityClass(get_class($object));
		if(isset($options['help'])) {
			echo "Usage: ".$_SERVER['argv'][0]." [OPTIONS ...]\nOptions:\n";

			foreach($class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
				if(strpos($method->name, 'set') === 0) {
					$name = substr($method->name, 3);
				} elseif(strpos($method->name, 'get') === 0) {
					$name = substr($method->name, 3);
				} elseif(strpos($method->name, 'add') === 0) {
					$name = $method->name;
				} else {
					continue;
				}
				$name = self::toCLIName($name);
				$about = (new EntityMethod($ce->name.'::'.$method->name))->setClass($ce)->scan();
                $req = [];
				if($about->arguments) {
					if($about->required == 0) {
						printf("  --%-25s  %s\n", $name, $about->description);
                        $req[] = $name;
					}
					foreach($about->arguments as $argument) {
						/* @var EntityArgument $argument */
						if($argument->isOptional()) {
							$arg_name = $name.".".$argument->name."=".strtoupper(Types::getTypeCode($argument->type));
                            printf("  --%-25s  %s\n", $arg_name, $argument->description." (required --".implode(", --", $req).")");
                        } else {
                            $arg_name = $name.".".$argument->name;
                            $req[] = $arg_name;
                            printf("  --%-25s  %s\n", $arg_name, $argument->description);
                        }
					}
				} else {
					printf("  --%-25s  %s\n", $name, $about->description);
				}
			}
            return false;
		}
		foreach($options as $option => $values) {
            $method = str_replace('-', '', $option);
            if(strpos($method, 'add') === 0) {
                self::_call([$object, $method], $values, $option);
            } elseif(method_exists($object, 'set'.$method)) {
                self::_call([$object, 'set'.$method], $values, $option);
            } elseif(method_exists($object, 'get'.$method)) {
                echo self::_call([$object, 'get'.$method], $values, $option)."\n";
                return false;
            } else {
                throw new \InvalidArgumentException("Unknown option --$option");
            }
        }

        return true;
	}

    /**
     * @param array|callable $callback
     * @param array $params
     * @param string $option
     * @return mixed
     * @throws \InvalidArgumentException
     */
    private static function _call(array $callback, array $params, $option) {
        $about = (new EntityMethod(get_class($callback[0]).'::'.$callback[1]))->scan();
        $args = [];
        foreach($about->arguments as $name => $arg) {
            if(array_key_exists($name, $params)) {
                $args[] = $params[$name];
                unset($params[$name]);
            } elseif($arg->isOptional()) {
                $args[] = $arg->default_value;
            } else {
                throw new \InvalidArgumentException("Required option --$option.$name");
            }
        }
        if($params) {
            throw new \InvalidArgumentException("Unknown option --$option.".key($params));
        }
        return call_user_func_array($callback, $args);
    }

	/**
	 * @return array
	 * @throws \InvalidArgumentException
	 */
	private static function _parse() {
		$options = [];
		$args = $_SERVER['argv'];
		array_shift($args);
		foreach($args as $arg) {
			if(strpos($arg, '--') !== 0) {
				throw new \InvalidArgumentException("Invalid argument $arg");
			}
			$arg = ltrim($arg, '--');
			if(strpos($arg, '=')) {
				list($arg, $value) = explode('=', $arg, 2);
                if(array_key_exists(strtolower($value), self::$_reserved)) {
                    $value = self::$_reserved[strtolower($value)];
                }
			} else {
				$value = null;
			}

            if(strpos($arg, '.')) {
                list($arg, $param) = explode(".", $arg, 2);
                $options[$arg][$param] = $value;
            } elseif(!isset($args[$arg])) {
                $options[$arg] = [];
            }
		}
		return $options;
	}

	/**
	 * @param string $name
	 * @return string
	 */
	public static function toCLIName($name) {
		$name = preg_replace('/([A-Z])/', '-$1', $name);
		return strtolower(ltrim($name, '-'));
	}

	/**
	 * @param string $name
	 * @return mixed
	 */
	public static function toCanonicalName($name) {
		return str_replace('-', '', $name);
	}
} 