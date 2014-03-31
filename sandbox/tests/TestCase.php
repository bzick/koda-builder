<?php

namespace Koda\Sandbox;


class TestCase extends \PHPUnit_Framework_TestCase {

    public function assertDefined($const_name, $value) {
        $this->assertTrue(defined($const_name));
        $this->assertSame($value, constant($const_name));
    }

    public function assertArguments($callable, $arguments) {
        if(is_array($callable) || strpos($callable, "::")) {
            if(is_string($callable)) {
                $callable = explode("::", $callable);
            }
            $ref = new \ReflectionMethod($callable[0], $callable[1]);
        } else {
            $ref = new \ReflectionFunction($callable);
        }

        foreach($ref->getParameters() as $name => $paremeter) {

        }
    }
}

class_exists('Koda');