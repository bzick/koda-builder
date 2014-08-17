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
     * @ini resources_dir
     */
    public $resources_dir;


	public $convert_path;
	public $ext_path;
	public $mdocs_path;
	public $mdocs_index = false;
	public $compile_path;
	public $debug;

    /**
     * @param string $root
     * @throws LogicException
     * @internal param string $config_path
     */
    public function __construct($root = null) {
        $this->root = $root?:getcwd();
        $this->resources_dir = __DIR__.'/../resources';
	    $this->ext_path = $this->root.'/build';
	    set_exception_handler([$this, 'fatal']);
    }

	public function fatal(Exception $exception) {
		echo get_class($exception).": ".$exception->getMessage()."\n".$exception->getTraceAsString()."\n";
		exit(1);
	}

	/**
	 * Enable debug
	 */
	public function setDebug() {
		$this->debug = true;
	}

	/**
	 * Convert PHP library to PHP extension sources.
	 * @param string $path Path to the sources will be converted. By default is build/
	 */
	public function setConvert($path = null) {
		$this->convert_path = $path ?: $this->root.'/build';
	}

	/**
     * Build PHP extension sources to shared library
	 * @param string $path Path of final extension file. By default is build/modules/<project_name>.so
	 */
	public function setCompile($path = null) {
		$this->compile = true;
		$this->ext_path = $path;
	}

    /**
     * Generate markdown documentation
     * @param string $path path for documentation. By default is docs/
     * @param bool $index Add index.html file into documentation for html representation
     */
	public function setMdocs($path = null, $index = false) {
		$this->mdocs_path = $path ?: $this->root.'/docs';
        $this->mdocs_index = $index;
	}

	/**
	 * Get Koda version
	 * @return string
	 */
	public function getVersion() {
		return self::VERSION_STRING;
	}

    /**
     * Dump project to stdout
     * @return string
     */
    public function getDump() {
        $project = \Koda\Project::composer($this->root);
        $project->scan();
        return ToolKit::dump($project);
    }

    /**
     * Get project stats
     */
    public function getStats() {

    }

    /**
     * Dispatch CLI request
     */
    public function dispatch() {
        $project = \Koda\Project::composer($this->root);

        $project->scan();

	    if($this->convert_path) {
		    $compiler = new Koda\Compiler\ZendEngine();
		    $compiler->setBuildDir($this->convert_path);
		    $compiler->setResourcesDir($this->resources_dir);
		    $compiler->process($project);
	    }
	    if($this->mdocs_path) {
		    $mdocs = new \Koda\Compiler\MDocs();
		    $mdocs->setDir($this->mdocs_path);
		    $mdocs->process($project);
	    }
    }

}

/**
 * Helper, var_dump()
 * @param mixed $item
 */
function dump($item) {
//    ob_end_clean();
    error_log(ToolKit::dump($item)."\n");
    error_log((new Exception())->getTraceAsString()."\n");
}

/**
 * Helper, var_dump(); exit;
 * @param mixed $item
 */
function drop($item) {
//    ob_end_clean();
    error_log(ToolKit::dump($item)."\n");
    error_log((new Exception())->getTraceAsString()."\n");
    exit;
}
