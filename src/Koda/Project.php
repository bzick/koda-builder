<?php

namespace Koda;

use Koda\Entity\EntityClass;
use Koda\Entity\EntityConstant;
use Koda\Entity\EntityFile;
use Koda\Entity\EntityFunction;
use Koda\Entity\EntityGlobal;
use Koda\Entity\EntityModule;
use Symfony\Component\Finder\Finder;

/**
 * Project entity. Collection of all entities of the project.
 * @package Koda
 */
class Project implements EntityInterface {
    /**
     * Project name
     * @var string
     */
    public $name;
    /**
     * Programming name
     * @var string
     */
    public $alias;
    /**
     * Internal programming name
     * @var string
     */
    public $code;
    /**
     * Project version
     * Версия проекта
     * @var string
     */
    public $version = '0.0';

    /**
     * Project description
     * @var string
     */
    public $description;

    /**
     * Project PHP files
     * @var EntityFile[]
     */
    public $files     = [];

    /**
     * Class list (as ArrayObject)
     * @var EntityClass[]
     */
    public $classes;

    /**
     * Storage for global entities (e.g. functions and constants)
     * @var EntityGlobal
     */
    public $global;

    /**
     * Dependence on extensions
     * @var EntityModule[]
     */
    public $depends = [];

    /**
     * Create project from Composer
     * @param string $path path to project root
     * @return Project
     */
    public static function composer($path) {
        chdir($path);
        $composer = json_decode(FS::get($path."/composer.json"), true);
        require_once $path.'/vendor/autoload.php';
        $project = new self($composer["name"]);
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
                $project->files[realpath($dir)] = new EntityFile(realpath($dir), $project);
            } else {
                foreach(Finder::create()->files()->followLinks()->name('/\.php$/iS')->in($path."/".$dir) as $file) {
                    /* @var $file \SplFileInfo */
                    $project->files[$file->getRealPath()] = new EntityFile($file->getRealPath(), $project);
                }
            }
        }
        return $project;
    }

    /**
     * @param string $name
     */
    public function __construct($name) {
        $this->name = $name;
        $this->global = new EntityGlobal($this);
        $this->classes = new \ArrayObject();
    }

    /**
     * Set module dependency
     * @param string $module
     * @return EntityModule
     */
    public function addDepends($module) {
        if($module == "Core") { // Core is not dependence
            return new EntityModule($module);
        }
        return $this->depends[$module] = new EntityModule($module);
    }

    /**
     * Set project name and description
     * @param string $name
     * @param string $descr
     * @return $this
     */
    public function setName($name, $descr) {
        $this->name = $name;
        $this->description = $descr;
        return $this;
    }

    /**
     * Set project version
     * @param $version
     * @return $this
     */
    public function setVersion($version) {
        $this->version = $version;
        return $this;
    }

    /**
     * Search entities in project files
     */
    public function scan() {
        foreach($this->files as $file) {
            $file->scan();
            $this->_addEntities($file);
        }

        foreach($this->classes as $class) {
            if($class->parent) {
                $class->parent = $this->_resolveDepend($class->parent);
            }
            if($class->interfaces) {
                foreach($class->interfaces as &$interface) {
                    $interface = $this->_resolveDepend($interface);
                }
            }
            if($class->parents) {
                foreach($class->parents as &$parent) {
                    $parent = $this->_resolveDepend($parent);
                }
            }
        }
    }

    /**
     * Resort entities found in the file
     * @param EntityFile $file
     */
    private function _addEntities(EntityFile $file) {
        foreach($file->classes as $class) {
            $this->addClass($class);
        }
        foreach($file->constants as $constant) {
            $this->global->addConstant($constant);
        }
        foreach($file->functions as $function) {
            $this->global->addFunction($function);

        }
    }

    /**
     * Add class
     * @param Entity\EntityClass $class
     * @throws \LogicException
     */
    public function addClass(EntityClass $class) {
        if(isset($this->classes[$class->name])) {
            throw new \LogicException("{$class} already defined in ".$this->classes[$class->name]->line." (try to define in {$class->line})");
        } else {
            $this->classes[$class->name] = $class;
        }
    }

    /**
     * Resolve depend from class
     * @param \ReflectionClass $class
     * @return EntityClass
     */
    private function _resolveDepend(\ReflectionClass $class) {
        if($class->isInternal()) {
            $this->addDepends($class->getExtensionName());
            return new EntityClass($class->getName(), null, [null, 0]);
        } elseif(isset($this->classes[$class->getName()])) {
            return $this->classes[$class->getName()];
        } else {
            // todo: log the problem
            $file = $this->files[$class->getFileName()] = new EntityFile($class->getFileName(), $this);
            $file->scan();
            $this->_addEntities($file);
        }
    }

    public function dump($tab = "") {
        $classes = [];
        foreach($this->classes as $class) {
            $classes[] = $class->dump($tab.'    ');
        }
        return "Project {$this->name} {".
            "\n$tab    version {$this->version}".
            "\n$tab    alias {$this->alias} [internal {$this->code}]\n".
            "\n$tab    {$this->global->dump('    ')}\n".
            "\n$tab    ".implode("\n$tab    ", $classes)."\n".
        "\n{$tab}}";
    }

    public function __toString() {
        return 'Project '.$this->name;
    }

    public function log() {

    }
}