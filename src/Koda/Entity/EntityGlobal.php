<?php

namespace Koda\Entity;


use Koda\EntityInterface;
use Koda\Project;

class EntityGlobal implements EntityInterface {
    /**
     * @var Project
     */
    public $project;
    /**
     * Project global functions
     * @var EntityFunction[]
     */
    public $functions = [];

    /**
     * @var EntityConstant[]
     */
    public $constants = [];

    /**
     * @param Project $project
     */
    public function __construct(Project $project) {
        $this->project = $project;
    }

    /**
     * @inheritdoc
     */
    public function dump($tab = "") {
        $items = [];
        foreach($this->constants as $const) {
            $items[] = $const->dump($tab.'    ');
        }
        if($items) {
            $items[] = "";
        }
        foreach($this->functions as $function) {
            $items[] = $function->dump($tab.'    ');
        }
        return implode("\n$tab", $items);
    }

    /**
     * @inheritdoc
     */
    public function __toString() {
        return "global";
    }

    /**
     * Add global function
     * Добавляет глобальную функцию
     * @param EntityFunction $function
     * @throws \LogicException
     */
    public function addFunction(EntityFunction $function) {
        if(isset($this->functions[$function->name])) {
            throw new \LogicException("{$function} already defined in ".$this->functions[$function->name]->line." (try to define in {$function->line})");
        } else {
            $this->functions[$function->name] = $function;
        }
    }

    /**
     * Add global constant
     * Добавляет глобальную константу
     * @param EntityConstant $constant
     * @throws \LogicException
     */
    public function addConstant(EntityConstant $constant) {
        if(isset($this->constants[$constant->name])) {
            throw new \LogicException("{$constant} already defined in {$this->constants[$constant->name]->line} (try to define in {$constant->line})");
        } else {
            $this->constants[$constant->name] = $constant;
        }
    }
}