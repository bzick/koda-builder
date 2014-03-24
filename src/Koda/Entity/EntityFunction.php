<?php

namespace Koda\Entity;


use PhpParser\Lexer;
use PhpParser\Parser;

class EntityFunction {

    public $name;
    public $short;
    public $ns;
    public $line;
    public $aliases;
    public $body;
    public $stmts;

    public function __construct($name, $aliases, $line) {
        $this->aliases = $aliases;
        $this->name = $name;
        $this->line = $line;
        $func = new \ReflectionFunction($name);
        $this->short = $func->getShortName();
        $this->ns = $func->getNamespaceName();
    }

    public function setBody($body) {
        $this->body = $body;
        $parser = new Parser(new Lexer);
        $stmts = $parser->parse($this->body);
    }
} 