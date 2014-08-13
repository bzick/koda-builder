<?php
/**
 * Created by PhpStorm.
 * User: bzick
 * Date: 11.08.14
 * Time: 10:08
 */

namespace Koda\Entity;


use Koda\Line;

abstract class EntityAbstract {
    public $name;
    /**
     * @var Line
     */
    public $line;
    /**
     * @var
     */
    public $description;

    public function setLine(Line $line) {
        $this->line = $line;
        return $this;
    }

    public function getLine() {
        return $this->line;
    }

    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    public function info($message) {

    }

    public function notice($message) {

    }

    public function warning($message) {

    }


    abstract public function dump($tab = "");
    abstract public function __toString();
} 