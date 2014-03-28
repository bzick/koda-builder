<?php
use Koda\ToolKit;

class Koda {
    const VERSION_STRING = "0.1";
    public $config = [];
    public $root;
    public $resources_dir;

    /**
     * @param string $root
     * @throws LogicException
     * @internal param string $config_path
     */
    public function __construct($root = null) {
        $this->root = $root;
        $this->resources_dir = __DIR__.'/../resources';
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

        $compiler = new Koda\Compiler\ZendEngine();
        $compiler->setBuildDir($this->root.'/build');
        $compiler->setResourcesDir($this->resources_dir);
        $compiler->process($project);
    }

}

function dump($item) {
    echo ToolKit::dump($item)."\n";
    echo (new Exception())->getTraceAsString()."\n";
}

function drop($item) {
    echo ToolKit::dump($item)."\n";
    echo (new Exception())->getTraceAsString()."\n";
    exit;
}
