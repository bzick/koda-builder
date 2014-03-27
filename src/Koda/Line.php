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
}