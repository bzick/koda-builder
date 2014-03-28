<?php

namespace Koda\Compiler;


use Koda\Compiler\ZendEngine\Dumper;
use Koda\Project;

class ZendEngine {

    public $build_dir;
    public $ext_path;
    public $resources_dir;


    public function setBuildDir($build_dir) {
        if(!file_exists($build_dir)) {
            mkdir($build_dir);
        } elseif(!is_dir($build_dir)) {
            throw new \LogicException("Compile dir $build_dir unavailable");
        }
        $this->build_dir = realpath($build_dir);
        return $this;
    }

    public function setResourcesDir($dir) {
        $this->resources_dir = $dir;
        return $this;
    }

    public function process(Project $project) {
        $dumper = new Dumper($project, $this);
        $dumper->dump();
    }
}