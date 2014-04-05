<?php
use Koda\ToolKit;

/**
 * Class Koda
 */
class Koda {
    const VERSION_STRING = "0.1";
    /**
     * Some configuration. unstructured yet
     * @var array
     */
    public $config = [];
    /**
     * Project root path
     * @var string
     */
    public $root;
    /**
     * Path to resources of Koda
     * @var string
     */
    public $resources_dir;

    /**
     * @param string $root
     * @throws LogicException
     * @internal param string $config_path
     */
    public function __construct($root = null) {
        $this->root = $root?:getcwd();
        $this->resources_dir = __DIR__.'/../resources';
    }

    /**
     * Dispatch CLI request
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

/**
 * Helper, var_dump()
 * @param mixed $item
 */
function dump($item) {
    echo ToolKit::dump($item)."\n";
    echo (new Exception())->getTraceAsString()."\n";
}

/**
 * Helper, var_dump(); exit;
 * @param mixed $item
 */
function drop($item) {
    echo ToolKit::dump($item)."\n";
    echo (new Exception())->getTraceAsString()."\n";
    exit;
}
