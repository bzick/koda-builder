<?php
namespace Koda\Compiler;


use Koda\Entity\Flags;
use Koda\FS;
use Koda\Project;

class MDocs {

    public $dir;

	public function setDir($dir) {
        if(!file_exists($dir)) {
            if(!mkdir($dir, 0755, true)) {
                throw new \RuntimeException("Could not create directory $dir for mdocs");
            }
        }
        $this->dir = $dir;
	}

	public function process(Project $project) {
        if($project->classes) {
            if(!file_exists("$this->dir/classes")){
                if(!mkdir("$this->dir/classes", 0755, true)) {
                    throw new \RuntimeException("Could not create directory {$this->dir}/classes for mdocs");
                }
            }
            foreach($project->classes as $class) {
                $doc = [];
                $doc["header"] = Flags::decode($class->flags & Flags::CLASS_TYPES) . " ".$class->name;
                $doc[] = str_pad("", strlen($doc["header"]), "=");
                $doc[] = "";
                if($class->parent) {
                    $doc[] = "Parent: [".$class->parent->name."](#xxx)";
                }
                if($class->interfaces) {
                    $doc[] = "Interfaces: ".implode(", ", array_keys($class->interfaces));
                }
                if($class->traits) {
                    $doc[] = "Traits: ".implode(", ", array_keys($class->traits));
                }

                $doc[] = $class->description;

                if($class->constants) {
                    $doc[] = "";
                    $doc[] = "## Constants";
                    foreach($class->constants as $constant) {
                        $doc[] = "**{$constant->short}** `{$constant->value}`";
                    }
                }

                if($class->properties) {
                    $doc[] = "";
                    $doc[] = "## Properties";
                    foreach($class->properties as $property) {
                        $doc[] = "**{$property->name}** `{$property->value}`";
                    }
                }

                if($class->methods) {
                    $doc[] = "";
                    $doc[] = "## Methods";
                    foreach($class->methods as $method) {
                        $doc[] = $method->dump()." {$method->description}";
                    }
                }

                FS::put("$this->dir/classes/".str_replace('\\', '-', $class->name).'.md', implode("\n", $doc));
            }
        }
	}

} 