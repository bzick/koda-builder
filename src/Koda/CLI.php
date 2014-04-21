<?php

namespace Koda;


use Koda\Entity\EntityArgument;
use Koda\Entity\EntityClass;
use Koda\Entity\EntityMethod;

class CLI {

	/**
	 * @param object $object
	 * @throws \InvalidArgumentException
	 */
	public static function configure($object) {
		$options = self::_parse();
		$class = new \ReflectionClass(get_class($object));
		$ce    = new EntityClass(get_class($object));
		if(array_key_exists('help', $options)) {
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
				if($about->arguments) {
					$argument = current($about->arguments);
					/* @var EntityArgument $argument */
					if($argument->isOptional()) {
						$name .= "[=".strtoupper($argument->name)."]";
					} else {
						$name .= "=".strtoupper($argument->name);
					}
				}
				printf("  --%-25s  %s\n", $name, $about->description);
			}
			exit;
		}

		foreach($options as $option => $value) {
			$method = self::toCanonicalName($option);
			if(strpos($method, 'add') === 0) {
				call_user_func([$object, $method], $value);
			} elseif(method_exists($object, 'set'.$method)) {
				call_user_func([$object, 'set'.$method], $value);
			} elseif(method_exists($object, 'get'.$method)) {
				echo call_user_func([$object, 'get'.$method], $value)."\n";
			} else {
				throw new \InvalidArgumentException("Unknown option --$option");
			}
		}
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
			} else {
				$value = null;
			}
			$options[$arg] = $value;
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