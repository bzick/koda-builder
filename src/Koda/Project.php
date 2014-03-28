<?php

namespace Koda;

use Koda\Entity\EntityClass;
use Koda\Entity\EntityConstant;
use Koda\Entity\EntityFile;
use Koda\Entity\EntityFunction;
use Koda\Entity\EntityModule;
use Symfony\Component\Finder\Finder;

class Project implements EntityInterface {
    public $name;
    public $alias;
    public $code;

    public $version = '0.0';

    public $description;

    /**
     * @var EntityFile[]
     */
    public $files     = [];

    /**
     * @var EntityFunction[]
     */
    public $functions = [];

    /**
     * @var EntityFunction[]
     */
    public $callable = [];

    /**
     * @var EntityClass[]
     */
    public $classes   = [];

    /**
     * @var EntityConstant[]
     */
    public $constants = [];

    /**
     * @var EntityModule[]
     */
    public $depends = [];

    /**
     * @param string $path
     * @return Project
     */
    public static function composer($path) {
        chdir($path);
        $composer = json_decode(FS::get($path."/composer.json"), true);
        require_once $path.'/vendor/autoload.php';
        $project = new self();
        $project->name = $composer["name"];
        list($vendor, $name) = explode("/", $project->name);
        if($vendor == $name) {
            $project->alias = ucfirst($name);
            $project->code  = strtolower($name);
        } else {
            $project->alias = ucfirst($vendor).ucfirst($name);
            $project->code  = strtolower($vendor)."_".strtolower($name);
        }
        $project->description = $composer["description"];
        if(isset($composer["config"]["koda"]["version"])) {
            $ver = $composer["config"]["koda"]["version"];
            if(is_callable($ver)) {
                $project->version = call_user_func($ver);
            } else {
                $project->version = exec($ver);
            }
        }
        $paths = [];
        foreach($composer["autoload"] as $loader) {
            $paths = array_merge($paths, array_values($loader));
        }
        foreach($composer["require"] as $require => $version) {
            if(strpos($require, "ext-") === 0) {
                $project->addDepends(substr($require, 4))->setRequired();
            }
        }
        foreach($composer["suggest"] as $suggest => $comment) {
            if(strpos($suggest, "ext-") === 0) {
                $project->addDepends(substr($suggest, 4))->setOptional();
            }
        }
        foreach($paths as $dir) {
            if(is_file($dir)) {
                $project->files[realpath($dir)] = new EntityFile(realpath($dir));
            } else {
                foreach(Finder::create()->files()->followLinks()->name('/\.php$/iS')->in($path."/".$dir) as $file) {
                    /* @var $file \SplFileInfo */
                    $project->files[$file->getRealPath()] = new EntityFile($file->getRealPath());
                }
            }
        }
        return $project;
    }

    /**
     * Set module dependency
     * @param string $module
     * @return EntityModule
     */
    public function addDepends($module) {
        return $this->depends[$module] = new EntityModule($module);
    }

    /**
     * @param string $name
     * @param string $descr
     * @return $this
     */
    public function setName($name, $descr) {
        $this->name = $name;
        $this->description = $descr;
        return $this;
    }

    public function setVersion($version) {
        $this->version = $version;
        return $this;
    }

    public function scan() {
        foreach($this->files as $file) {
            /* @var EntityFile $file */
            $file->scan();
            foreach($file->classes as $class) {
                /* @var EntityClass $class */
                if(isset($this->classes[$class->name])) {
                    throw new \LogicException("Class {$class->name} already defined in ".$this->classes[$class->name]->line." (try to define in {$class->line})");
                } else {
                    $this->classes[$class->name] = $class;
                    foreach($class->constants as $constant) {
                        $this->constants[$constant->name] = $constant;
                    }
                    foreach($class->methods as $method) {
                        $this->callable[$method->name] = $method;
                    }
                }
            }
            foreach($file->constants as $constant) {
                if(isset($this->constants[$constant->name])) {
                    throw new \LogicException("Constant {$constant->name} already defined in ".$this->constants[$constant->name]->line." (try to define in {$class->line})");
                } else {
                    $this->constants[$constant->name] = $constant;
                }
            }
            foreach($file->functions as $function) {
                if(isset($this->functions[$function->name])) {
                    throw new \LogicException("Function {$function->name} already defined in ".$this->functions[$function->name]->line." (try to define in {$function->line})");
                } else {
                    $this->functions[$function->name] = $function;
                    $this->callable[$function->name] = $function;
                }
            }
        }
    }

    public function dump($tab = "") {
        $constants = [];
        foreach($this->constants as $const) {
            if(!$const->class) {
                $constants[] = $const->dump($tab.'    ');
            }
        }
        $functions = [];
        foreach($this->callable as $function) {
            if(!$function->class) {
                $functions[] = $function->dump($tab.'    ');
            }
        }

        $classes = [];
        foreach($this->classes as $class) {
            $classes[] = $class->dump($tab.'    ');
        }
        return "Project {$this->name} {".
            "\n$tab    version {$this->version}".
            "\n$tab    alias {$this->alias}\n".
            "\n$tab    ".implode("\n$tab    ", $constants)."\n".
            "\n$tab    ".implode("\n$tab    ", $functions)."\n".
            "\n$tab    ".implode("\n$tab    ", $classes)."\n".
        "\n{$tab}}";
    }

    public function __toString() {
        return 'Project '.$this->name;
    }
}