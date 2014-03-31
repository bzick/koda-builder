<?php

namespace Koda\Sandbox;

class NamesTest extends TestCase {

    public function testConstants() {
        $this->assertDefined('Koda\Sandbox\FIVE', 5);
        $this->assertDefined('Koda\Sandbox\FLOAT_FIVE', 5.5);
        $this->assertDefined('Koda\Sandbox\STRING_FIVE', 'five');
    }

    public function testFunction() {
        $this->assertTrue(function_exists('Koda\Sandbox\simple_function'));
        $this->assertArguments('Koda\Sandbox\simple_function', ["x", "y"]);
    }
} 