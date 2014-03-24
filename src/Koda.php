<?php

class Koda {
    public $config = [];
    public $root = __DIR__;

    /**
     * @param null $root
     * @throws LogicException
     * @internal param string $config_path
     */
    public function __construct($root = null) {
        $this->root = $root;
    }

    /**
     *
     */
    public function dispatch() {
        $options = getopt('h', array(
            "help::",
            "convert::",
            "tmp:",
        )) + ['tmp' => '/tmp'];
        if(isset($options['help']) || isset($options['h'])) {
            $file = basename($_SERVER['PHP_SELF']);
            echo "
Usage: $file --convert [--tmp=/tmp]
Help:  $file -h|--help\n";
            exit;
        }

        $project = \Koda\Project::composer($this->root);

        $project->scan();

    }

    /**
     * @param string $tmp
     */
    public function convert($tmp) {
        // Get classmap from composer
        $classes = $this->parseClasses();

        $extension = new \Koda\Extension($this);

        foreach($classes as $class => $path) {
            $this->classes[$class] = new TxClass($class, $path);
            foreach($this->classes[$class]->uses as $class) {
                if(isset($classes[ $class ])) {
                    continue;
                } elseif(class_exists($class, false)) {
                    $uses = new ReflectionClass($class);
                    if($ext = $uses->getExtension()) {
                        $this->classes[$class]->addDepends($ext);
                    }
                }

                throw new LogicException("Unknown class $class");
            }
            $extension->addClass($class);

            $this->put($class->basename.".h", $class->headerFile());
        }
        $this->put("comfig.m4", $extension->configM4());
        $this->put("php_{$this->config->alias}.h", $extension->headerFile());
        $this->put("php_{$this->config->alias}.c", $extension->cFile());
    }

    public function parseClasses() {
        $classes = array();
        foreach($this->config['autoload'] as $rule) {
            // ... some code ... load from autoload of projects and packages
            $classes += \Toxen\ClassMapGenerator::createMap($path);
        }

        return $classes;
    }
}


function dump($value) {

}