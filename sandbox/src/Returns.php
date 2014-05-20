<?php

namespace Koda\Sandbox;


class Returns {
    public function returnInt() {
        return 5;
    }

    public function returnDouble() {
        return 5.5;
    }

    public function returnNegative() {
        return -5;
    }

    public function returnString() {
        return "some string";
    }

    public function returnTrue() {
        return true;
    }

    public function returnFalse() {
        return false;
    }

    public function returnNULL() {
        return null;
    }

	public function returnVar($a) {
		return $a;
	}
}