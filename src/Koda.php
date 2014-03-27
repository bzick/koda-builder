<?php
use Koda\Dumper;

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


        dump($project);
    }

}

function dump($item) {
    echo Dumper::dump($item)."\n";
    echo (new Exception())->getTraceAsString()."\n";
}

function drop($item) {
    echo Dumper::dump($item)."\n";
    echo (new Exception())->getTraceAsString()."\n";
    exit;
}
