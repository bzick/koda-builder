<?php

namespace Koda;


use Koda\Entity\EntityFile;

class Line {

    /**
     * @var EntityFile entity's file
     */
    public $file;
    /**
     * @var int start line
     */
    public $line = 0;
    /**
     * @var int
     */
    public $length = 0;

    /**
     * @param EntityFile $file
     * @param int $line
     */
    public function __construct(EntityFile $file, $line = 0) {
        $this->file = $file;
        $this->line = $line;
    }

    /**
     * @param int $number
     * @return Line
     */
    public function jump($number) {
        $line = clone $this;
        $line->line = $number;
        return $line;
    }

    /**
     * @return string
     */
    public function __toString() {
        return $this->file->getBasePath().":".$this->line;
    }
}