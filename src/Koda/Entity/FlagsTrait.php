<?php

namespace Koda\Entity;


trait FlagsTrait {
    public $flags = 0;

    /**
     * @param int $flags
     * @return $this
     */
    public function setFlags($flags) {
        $this->flags = $flags;
        return $this;
    }

    /**
     * @param int $flag
     * @return $this
     */
    public function addFlag($flag) {
        $this->flags |= $flag;
        return $this;
    }

    public function isAbstract() {
        return (bool)($this->flags & Flags::IS_ABSTRACT);
    }

    public function isFinal() {
        return (bool)($this->flags & Flags::IS_FINAL);
    }

    public function isPublic() {
        return (bool)($this->flags & Flags::IS_PUBLIC);
    }

    public function isPrivate() {
        return (bool)($this->flags & Flags::IS_PRIVATE);
    }

    public function isProtected() {
        return (bool)($this->flags & Flags::IS_PROTECTED);
    }

    /**
     * Checks if the class is an interface
     * @return int
     */
    public function isInterface() {
        return $this->flags & Flags::IS_INTERFACE;
    }

    /**
     * Checks if the class is an trait
     * @return int
     */
    public function isTrait() {
        return $this->flags & Flags::IS_TRAIT;
    }

    /**
     * Checks if the class is an plain class
     * @return int
     */
    public function isClass() {
        return $this->flags & Flags::IS_CLASS;
    }

} 