<?php

namespace Koda\Sandbox;

class NamesTest extends TestCase {

    public function testConstants() {
        $this->assertDefined('Koda\Sandbox\FIVE', 5);
        $this->assertDefined('Koda\Sandbox\FLOAT_FIVE', 5.5);
        $this->assertDefined('Koda\Sandbox\STRING_FIVE', 'five');
    }

    public function testFunction() {
        $this->assertTrue(function_exists('Koda\Sandbox\simple_multi'));
        $this->assertTrue(function_exists('KodaSandbox\simple_div'));
    }

    public function testClass() {
        $this->assertTrue(class_exists('Koda\Sandbox\Names', false));
        $this->assertTrue(class_exists('KodaSandbox\Names', false));
        $this->assertTrue(method_exists('Koda\Sandbox\Names', 'publicMethod'));
    }
} 