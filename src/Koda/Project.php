<?php
/**
 * Created by PhpStorm.
 * User: bzick
 * Date: 16.03.14
 * Time: 1:51
 */

namespace Koda;


use Koda\Entity\EntityClass;
use Koda\Entity\EntityFile;
use Symfony\Component\Finder\Finder;

class Project {

    public $name;

    public $version = 0.0;

    public $description;

    public $files     = [];

    public $functions = [];

    public $classes   = [];

    public $methods   = [];

    public $consts    = [];

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
        $project->description = $composer["description"];
        $paths = [];
        foreach($composer["autoload"] as $loader) {
            $paths = array_merge($paths, array_values($loader));
        }
        foreach($paths as $dir) {
            if(is_file($dir)) {
                $project->files[realpath($dir)] = new EntityFile(realpath($dir));
            } else {
                foreach(Finder::create()->files()->followLinks()->name('/\.php$/')->in($path."/".$dir) as $file) {
                    /* @var $file \SplFileInfo */
                    $project->files[$file->getRealPath()] = new EntityFile($file->getRealPath());
                }
            }
        }
        return $project;
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
                if(isset($this->classes[$class->name])) {
                    throw new \LogicException("Class {$class->name} already loaded form ".$this->classes[$class->name]->file." (try loads from {$class->file})");
                } else {

                }
            }
            var_dump($file->classes); exit;
        }
    }
} 