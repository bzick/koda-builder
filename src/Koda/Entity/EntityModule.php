<?php

namespace Koda\Entity;

class EntityModule extends EntityAbstract {
    const DEP_REQUIRE = 1;
    const DEP_CONFLICTS = 2;
    const DEP_OPTIONAL = 3;

    public $version;
    public $type = self::DEP_REQUIRE;

    public function __construct($name) {
        $this->name = $name;
    }

    public function setVersion($version) {
        $this->version = $version;
        return $this;
    }

    public function setRequired() {
        $this->type = self::DEP_REQUIRE;
        return $this;
    }

    public function setOptional() {
        $this->type = self::DEP_OPTIONAL;
        return $this;
    }

    public function setConflicts() {
        $this->type = self::DEP_CONFLICTS;
        return $this;
    }

    public function dump($tab = "") {
        return "module {$this->name}";
    }

    public function __toString() {
        return "module {$this->name} {$this->version}";
    }
}