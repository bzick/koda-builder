<?php

namespace Koda\Compiler\ZendEngine;


use Koda\Compiler\ZendEngine;
use Koda\Entity\EntityArgument;
use Koda\Entity\EntityConstant;
use Koda\Entity\EntityFunction;
use Koda\Entity\Types;
use Koda\FS;
use Koda\Project;
use Koda\ToolKit;

class Dumper {
    public $project;
    public $code;
    public $CODE;
    public $sources;

    /**
     * @param Project $project
     * @param \Koda\Compiler\ZendEngine $ze
     */
    public function __construct(Project $project, ZendEngine $ze) {
        $this->ze = $ze;
        $this->project = $project;
        $this->CODE = strtoupper($project->code);
        $this->code = strtolower($project->code);

    }

    public function dump() {
        /* dump project map */
        $this->put('report/project_map.txt', ToolKit::dump($this->project));

        /* import helpers and resources */
        $this->import(['gitignore' => '.gitignore']);
//        $this->import('koda_helper.h');
//        $this->import('koda_helper.c', true);

        /* dump main module C-file */
        $this->file('php_'.$this->code, $this->extH(), $this->extC());

        /* dump classes */
        foreach($this->project->classes as $class) {
            $this->file(str_replace('\\', '/', $class->name), $this->classH($class), $this->classC($class));
        }

        /* generate config.m4 for unix-like system, see http://www.php.net/manual/en/internals2.buildsys.configunix.php */
        $this->put('config.m4', $this->m4());

        /* generate config.w32 for windows, see http://www.php.net/manual/en/internals2.buildsys.configwin.php */
        $this->put('config.w32', $this->w32());
    }

    public function import($file, $is_source = false) {
        if(is_array($file)) {
            copy($this->ze->resources_dir.'/'.key($file), $this->ze->build_dir.'/'.current($file));
        } else {
            copy($this->ze->resources_dir.'/'.$file, $this->ze->build_dir.'/'.$file);
        }
        if($is_source) {
            $this->sources[] = $file;
        }
    }

    public function file($basename, $h, $c) {
        $this->put($basename.'.h', $h);
        $this->put($basename.'.c', $c);
        $this->sources[] = $basename.'.c';
    }

    public function put($file, $content) {
        $dir_name = dirname($file);
        if($dir_name != "." && !is_dir($this->ze->build_dir.'/'.$dir_name)) {
            mkdir($this->ze->build_dir.'/'.$dir_name, 0755, true);
        }
        FS::put($this->ze->build_dir.'/'.$file, $content);
    }


    public function m4() {
        $project = $this->project;
        $sources = implode(" ", $this->sources);
        $date = date("Y-m-d H:i:s");
        ob_start();
        echo <<<M4
dnl Koda compiler, {$date}.

PHP_ARG_WITH({$this->code}, for {$project->name} support,
[  --with-{$this->code}             Include {$project->name} support])

CFLAGS="\$CFLAGS -Wall -g3 -ggdb -O0"

if test "\$PHP_{$this->CODE}" != "no"; then
    PHP_ADD_INCLUDE(.)
    PHP_SUBST({$this->CODE}_SHARED_LIBADD)
    PHP_NEW_EXTENSION({$this->code}, "{$sources}", \$ext_shared,, \$CFLAGS)
fi
M4;
        return ob_get_clean();
    }

    public function w32() {
        ob_start();
        return ob_get_clean();
    }

    /**
     * Generate module main H file
     * @return string
     */
    public function extH() {
        ob_start();
        echo <<<HEADER
#ifndef PHP_{$this->CODE}_H
#define PHP_{$this->CODE}_H

extern zend_module_entry {$this->code}_module_entry;
#define phpext_{$this->code}_ptr &{$this->code}_module_entry

#define PHP_{$this->CODE}_VERSION "{$this->project->version}"

#ifdef ZTS
#  include "TSRM.h"
#endif

/* Global functions */

HEADER;
        foreach($this->project->functions as $function) {
            if($function->class) {
                continue;
            }
            echo "PHP_FUNCTION(php_{$function->short});\n";
        }

        echo <<<HEADER

/* Std module functions */
PHP_MINIT_FUNCTION({$this->code});
PHP_MINFO_FUNCTION({$this->code});

#endif	/* PHP_{$this->CODE}_H */\n
HEADER;
        return ob_get_clean();
    }


    /**
     * Generate module main C file
     * @return string
     */
    public function extC() {
        ob_start();
        $function_table = "NULL";
        $depends = "NULL";
        $koda_version = \Koda::VERSION_STRING;
        echo <<<SOURCE
#ifdef HAVE_CONFIG_H
#  include "config.h"
#endif

/* PHP */
#include "php.h"
#include "ext/standard/info.h"

/* Extension */
#include "php_{$this->code}.h"

#ifdef COMPILE_DL_{$this->CODE}
    ZEND_GET_MODULE({$this->code})
#endif

SOURCE;
        if($this->project->functions) {
            echo <<<SOURCE

/* Global functions */

SOURCE;
            $entry_table = [];
            foreach($this->project->functions as $function) {
                if($function->class) {
                    continue;
                }
                echo <<<SOURCE

/* proto {$function->dump()} */
PHP_FUNCTION({$function->short}) {
    // coming soon ...
}

ZEND_BEGIN_ARG_INFO_EX(arginfo_{$function->short}, 0, {$function->isReturnRef()},  {$function->required})\n
SOURCE;
                foreach($function->arguments as $argument) {
                    echo <<<SOURCE
    {$this->_arginfo($argument)} // $argument\n
SOURCE;
                }
                echo <<<SOURCE
ZEND_END_ARG_INFO();

SOURCE;
                $entry_table[] = $this->_fe($function);
            }
            $entry_table = implode("    ", $entry_table);
            echo <<<SOURCE

/* Register functions */
const zend_function_entry {$this->code}_functions[] = {
    {$entry_table}
    ZEND_FE_END
};

SOURCE;
            $function_table = "{$this->code}_functions";
        }
        echo <<<SOURCE

/* Declare module */
zend_module_entry {$this->code}_module_entry = {
    STANDARD_MODULE_HEADER_EX,
    NULL,
    {$depends},  // dependencies
    "{$this->code}",  // human readable module name
    {$function_table},  // list of global functions
    PHP_MINIT({$this->code}),  // module constructor
    NULL,  // module destructor (auto)
    NULL,  // on start request callback
    NULL,  // on end request callback
    PHP_MINFO({$this->code}),  // info for phpinfo()
    "{$this->project->version}",  // module version
    STANDARD_MODULE_PROPERTIES
};

/* Init module */
PHP_MINIT_FUNCTION({$this->code}) {

    /* Constants */\n
SOURCE;
        foreach($this->project->constants as $constant) {
            if($constant->class) {
                continue;
            }
            echo <<<SOURCE
    {$this->_constant($constant)}\n
SOURCE;
        }
        echo <<<SOURCE

    return SUCCESS;
}

/* Build phpinfo table */
PHP_MINFO_FUNCTION({$this->code}) {

    php_info_print_table_start();
    php_info_print_table_header(2, "{$this->project->name} support", "enabled");
    php_info_print_table_header(2, "{$this->project->name} version", "{$this->project->version}");
    php_info_print_table_header(2, "{$this->project->name} with Koda", "{$koda_version}");
    php_info_print_table_end();

}
SOURCE;
        return ob_get_clean();
    }

    /**
     * @param EntityArgument $argument
     * @return string
     */
    private function _arginfo(EntityArgument $argument) {
        switch($argument->type) {
            case Types::OBJECT:
                return "ZEND_ARG_OBJ_INFO({$argument->isRef()}, {$argument->name}, {$argument->instance_of}, 1)";
            case Types::ARR:
                return "ZEND_ARG_ARRAY_INFO({$argument->isRef()}, {$argument->name}, 1)";
            default:
                return "ZEND_ARG_INFO({$argument->isRef()}, {$argument->name})";
        }
    }

    /**
     * Zend function entry macros
     * @param EntityFunction $function
     * @return string
     */
    public function _fe(EntityFunction $function) {
        if($function->ns) {
            return "ZEND_NS_FE(\"".addslashes($function->ns)."\", {$function->short}, arginfo_{$function->short})";
        } else {
            return "ZEND_FE({$function->short}, arginfo_{$function->short})";
        }
    }

    /**
     * Convert constant to ZE representation
     * @param EntityConstant $constant
     * @return string
     * @throws \LogicException
     */
    private function _constant(EntityConstant $constant) {

        if($constant->ns) {
            $name = '"'.addslashes($constant->ns).'", "'.addslashes($constant->short).'"';
            $prefix = 'REGISTER_NS';
        } else {
            $name = '"'.addslashes($constant->name).'"';
            $prefix = 'REGISTER';
        }
        switch($constant->type) {
            case Types::INT:
                return "{$prefix}_LONG_CONSTANT({$name}, {$constant->value}, CONST_CS | CONST_PERSISTENT);";
            case Types::STRING:
                return "{$prefix}_STRING_CONSTANT({$name}, \"".addslashes($constant->value)."\", CONST_CS | CONST_PERSISTENT);";
            case Types::BOOLEAN:
                return "{$prefix}_BOOL_CONSTANT({$name}, ".intval($constant->value).", CONST_CS | CONST_PERSISTENT);";
            case Types::NIL:
                return "{$prefix}_NULL_CONSTANT({$name}, CONST_CS | CONST_PERSISTENT);";
            case Types::DOUBLE:
                return "{$prefix}_DOUBLE_CONSTANT({$name}, {$constant->value}, CONST_CS | CONST_PERSISTENT);";
            default:
                throw new \LogicException("Unknown type $constant");
        }
    }

    public function classH() {
        ob_start();
        return ob_get_clean();
    }

    public function classC() {
        ob_start();
        return ob_get_clean();
    }

} 