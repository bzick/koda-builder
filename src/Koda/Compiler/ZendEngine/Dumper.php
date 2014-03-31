<?php

namespace Koda\Compiler\ZendEngine;


use Koda\Compiler\ZendEngine;
use Koda\Entity\EntityArgument;
use Koda\Entity\EntityClass;
use Koda\Entity\EntityConstant;
use Koda\Entity\EntityFunction;
use Koda\Entity\EntityMethod;
use Koda\Entity\EntityModule;
use Koda\Entity\EntityProperty;
use Koda\Entity\Flags;
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

    /**
     * Create sources
     */
    public function dump() {
        /* dump project map */
        $this->put('report/project_map.txt', ToolKit::dump($this->project));

        /* import helpers and resources */
        $this->import(['gitignore' => '.gitignore']);
        $this->import('koda_helper.h');
        $this->import('koda_helper.c', true);

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


    /**
     * m4 config for unix `configure`
     * @see http://docs.php.net/manual/en/internals2.buildsys.configunix.php
     * @return string
     */
    public function m4() {
        $project = $this->project;
        $sources = implode(" ", $this->sources);
        $date = date("Y-m-d H:i:s");
        $m4_alias = str_replace('_', '-', $this->code);
        ob_start();
        echo <<<M4
dnl Koda compiler, {$date}.

PHP_ARG_WITH({$this->code}, for {$project->name} support,
[  --with-{$m4_alias}             Include {$project->name} support])
PHP_ARG_ENABLE({$m4_alias}-debug, whether to enable debugging support in {$project->name},
[  --enable-{$m4_alias}-debug     Enable debugging support in {$project->name}], no, no)

CFLAGS="\$CFLAGS -Wall -g3 -ggdb -O0"

if test "\$PHP_{$this->CODE}" != "no"; then
    PHP_ADD_INCLUDE(.)
    PHP_SUBST({$this->CODE}_SHARED_LIBADD)
    PHP_NEW_EXTENSION({$this->code}, {$sources}, \$ext_shared,, \$CFLAGS)
fi
M4;
        return ob_get_clean();
    }

    /**
     * @see http://www.php.net/manual/en/internals2.buildsys.configwin.php
     * @return string
     */
    public function w32() {
        ob_start();
        return ob_get_clean();
    }

    /**
     * Generate module main H file
     * @return string
     */
    public function extH() {

        if($this->project->classes) {
            $init_classes = [];
            foreach($this->project->classes as $class) {
                $init_classes[] =  "PHP_MINIT_FUNCTION(init_{$class->cname}); // init class {$class->name}";
                $init_classes[] =  "PHP_MINIT_FUNCTION(load_{$class->cname}); // load class {$class->name}";
            }
            $init_classes = implode("\n", $init_classes)."\n";
        } else {
            $init_classes = "";
        }
        if($this->project->functions) {
            $functions = ["/* Global functions */"];
            foreach($this->project->functions as $function) {
                $functions[] = "PHP_FUNCTION(php_{$function->short});\n";
            }
            $functions = implode("\n", $functions)."\n";
        } else {
            $functions = "";
        }
        ob_start();
        echo <<<CONTENT
#ifndef PHP_{$this->CODE}_H
#define PHP_{$this->CODE}_H

extern zend_module_entry {$this->code}_module_entry;
#define phpext_{$this->code}_ptr &{$this->code}_module_entry

#define PHP_{$this->CODE}_VERSION "{$this->project->version}"

$functions
/* Std module functions */
PHP_MINIT_FUNCTION({$this->code});
$init_classes
PHP_MINFO_FUNCTION({$this->code});
#endif	/* PHP_{$this->CODE}_H */\n
CONTENT;
        return ob_get_clean();
    }


    /**
     * Generate module main C file
     * @return string
     */
    public function extC() {
        ob_start();
        $function_table = "NULL";
        $deps = "NULL";
        $koda_version = \Koda::VERSION_STRING;
        echo <<<HEADER
#ifdef HAVE_CONFIG_H
#  include "config.h"
#endif

/* PHP */
#include "php.h"
#include "ext/standard/info.h"

/* Extension */
#include "koda_helper.h"
#include "php_{$this->code}.h"

BEGIN_EXTERN_C();

#ifdef COMPILE_DL_{$this->CODE}
    ZEND_GET_MODULE({$this->code})
#endif

HEADER;
        /* Functions */
        if($this->project->functions) {
            echo "/* Global functions */\n";
            $entry_table = [];
            foreach($this->project->functions as $function) {
                if($function->class) {
                    continue;
                }
                if($function->arguments) {
                    $arginfo     = [];
                    foreach($function->arguments as $argument) {
                        $arginfo[] = $this->_arginfo($argument)." // {$argument->dump()}";
                    }
                    $arginfo = "\n    ".implode("\n    ", $arginfo);
                } else {
                    $arginfo = "";
                }
                echo <<<DEFINE_FUNTION

/* proto {$function->dump()} */
PHP_FUNCTION({$function->short}) {
    // coming soon ...
}

ZEND_BEGIN_ARG_INFO_EX(arginfo_{$function->short}, 0, {$function->isReturnRef()},  {$function->required}){$arginfo}
ZEND_END_ARG_INFO();

DEFINE_FUNTION;
                $entry_table[] = $this->_fe($function);
            }
            $entry_table = implode("\n    ", $entry_table);
            echo <<<REGISTER_FUNC

/* Register functions */
const zend_function_entry {$this->code}_functions[] = {
    {$entry_table}
    ZEND_FE_END
};

REGISTER_FUNC;
            $function_table = "{$this->code}_functions";
        }

        /* Dump depends */
        if($this->project->depends) {
            $depends = [];
            foreach($this->project->depends as $depend) {
                if($depend->type == EntityModule::DEP_REQUIRE) {
                    $depends[] = "ZEND_MOD_REQUIRED(\"{$depend->name}\")";
                } elseif($depend->type == EntityModule::DEP_OPTIONAL) {
                    $depends[] = "ZEND_MOD_OPTIONAL(\"{$depend->name}\")";
                } else {
                    $depends[] = "ZEND_MOD_CONFLICTS(\"{$depend->name}\")";
                }
            }
            $depends = implode("\n    ", $depends);
            echo <<<DEPENDS

/* Dependency */
static const zend_module_dep {$this->code}_depends[] = {
    $depends
    { NULL, NULL, NULL}
};

DEPENDS;
            $deps = "{$this->code}_depends";
        }
        echo <<<REGISTER_MODULE

/* Declare module */
zend_module_entry {$this->code}_module_entry = {
    STANDARD_MODULE_HEADER_EX,  // api, debug, zts, ...
    NULL,  // ini handler
    {$deps},  // dependencies
    "{$this->code}",  // human readable module name
    {$function_table},  // list of global functions
    PHP_MINIT({$this->code}),  // module constructor
    NULL,  // module destructor (auto)
    NULL,  // on start request callback
    NULL,  // on end request callback
    PHP_MINFO({$this->code}),  // info for phpinfo()
    "{$this->project->version}",  // module version
    STANDARD_MODULE_PROPERTIES  // id, flags, ...
};

REGISTER_MODULE;
        if($this->project->constants) {
            $constants = ['/* Constants */'];
            foreach($this->project->constants as $constant) {
                if($constant->class) {
                    continue;
                }
                $constants[] = "/* {$constant->dump()} */";
                $constants[] = $this->_constant($constant);
            }
            $constants = implode("\n    ", $constants);
        } else {
            $constants = "";
        }
        if($this->project->classes) {
            $inits = ['/* Classes */'];
            $loads = [];
            foreach($this->project->classes as $class) {
                $inits[] = "STARTUP_MODULE(init_{$class->cname}); // init {$class->name}";
                $loads[] = "STARTUP_MODULE(load_{$class->cname}); // load {$class->name}";
            }
            $inits = implode("\n    ", $inits);
            $loads = implode("\n    ", $loads);
        } else {
            $inits = $loads = "";
        }
        echo <<<INIT_MODULE
/* Init module */
PHP_MINIT_FUNCTION({$this->code}) {
    {$constants}

    {$inits}
    {$loads}

    return SUCCESS;
}

INIT_MODULE;
        echo <<<FOOTER

/* Build phpinfo table */
PHP_MINFO_FUNCTION({$this->code}) {

    php_info_print_table_start();
    php_info_print_table_header(2, "{$this->project->name} support", "enabled");
    php_info_print_table_header(2, "{$this->project->name} version", "{$this->project->version}");
    php_info_print_table_header(2, "{$this->project->name} with Koda", "{$koda_version}");
    php_info_print_table_header(2, "{$this->project->name} with debug", "yes");
    php_info_print_table_end();

}

END_EXTERN_C();
FOOTER;
        return ob_get_clean();
    }

    /**
     * @param EntityArgument $argument
     * @return string
     */
    private function _arginfo(EntityArgument $argument) {
        switch($argument->type) {
            case Types::OBJECT:
                return "ZEND_ARG_OBJ_INFO({$argument->isRef()}, {$argument->name}, \"".addslashes($argument->instance_of)."\", ".intval($argument->is_optional).")";
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

        $flags = ", CONST_CS | CONST_PERSISTENT";
        if($constant->class) {
            $prefix = 'REGISTER_CLASS';
            $name = 'ce_'.$constant->class->cname.', "'.addslashes($constant->short).'"';
            $flags = '';
        } elseif($constant->ns) {
            $name = '"'.addslashes($constant->ns).'", "'.addslashes($constant->short).'"';
            $prefix = 'REGISTER_NS';

        } else {
            $name = '"'.addslashes($constant->name).'"';
            $prefix = 'REGISTER';
        }
        switch($constant->type) {
            case Types::INT:
                return "{$prefix}_LONG_CONSTANT({$name}, {$constant->value}{$flags});";
            case Types::STRING:
                return "{$prefix}_STRING_CONSTANT({$name}, \"".addslashes($constant->value)."\"{$flags});";
            case Types::BOOLEAN:
                return "{$prefix}_BOOL_CONSTANT({$name}, ".intval($constant->value)."{$flags});";
            case Types::NIL:
                return "{$prefix}_NULL_CONSTANT({$name}{$flags});";
            case Types::DOUBLE:
                return "{$prefix}_DOUBLE_CONSTANT({$name}, {$constant->value}{$flags});";
            default:
                throw new \LogicException("Unknown type $constant");
        }
    }

    /**
     * Convert class property to ZE representation
     * @param \Koda\Entity\EntityProperty $property
     * @throws \LogicException
     * @return string
     */
    private function _property(EntityProperty $property) {
        static $marks = [
            Flags::IS_STATIC     => "ZEND_ACC_STATIC",
            Flags::IS_PUBLIC     => "ZEND_ACC_PUBLIC",
            Flags::IS_PROTECTED  => "ZEND_ACC_PROTECTED",
            Flags::IS_PRIVATE    => "ZEND_ACC_PRIVATE"
        ];
        $flags = [];
        foreach($marks as $mark => $flag) {
            if($property->flags & $mark) {
                $flags[] = $flag;
            }
        }
        $flags = implode(" | ", $flags);
        switch($property->type) {
            case Types::INT:
                return "REGISTER_CLASS_LONG_PROPERTY(ce_{$property->class->cname}, \"{$property->name}\", {$property->value}, {$flags});";
            case Types::STRING:
                return "REGISTER_CLASS_STRING_PROPERTY(ce_{$property->class->cname}, \"{$property->name}\", \"".addslashes($property->value)."\", {$flags});";
            case Types::BOOLEAN:
                return "REGISTER_CLASS_BOOL_PROPERTY(ce_{$property->class->cname}, \"{$property->name}\", ".intval($property->value).", {$flags});";
            case Types::NIL:
                return "REGISTER_CLASS_NULL_PROPERTY(ce_{$property->class->cname}, \"{$property->name}\", {$flags});";
            case Types::DOUBLE:
                return "REGISTER_CLASS_DOUBLE_PROPERTY(ce_{$property->class->cname}, \"{$property->name}\", {$property->value}, {$flags});";
            default:
                throw new \LogicException("Unknown type $property");
        }
    }

    /**
     * @param EntityClass $class
     * @return string
     */
    public function classH(EntityClass $class) {
        ob_start();
        $name = str_replace('\\', '_', $class->name);
        $NAME = strtoupper($name);
        $methods = [];
        foreach($class->methods as $method) {
            $methods[] = "PHP_METHOD({$name}, {$method->short});";
        }
        $methods = implode("\n", $methods);
        echo <<<CONTENT
#ifndef PHP_{$NAME}_H
#define PHP_{$NAME}_H

BEGIN_EXTERN_C();

/* Declare class entry */
extern zend_class_entry *ce_{$name};

/* Methods */
{$methods}

/* Init function */
PHP_MINIT_FUNCTION({$name});

END_EXTERN_C();

#endif	/* PHP_{$NAME}_H */\n
CONTENT;
        return ob_get_clean();
    }

    /**
     * @param EntityClass $class
     * @return string
     */
    public function classC(EntityClass $class) {
        $escaped = addslashes($class->name);
        $name = $class->cname;
        $path = str_replace('\\', '/', $class->name);
        ob_start();
        echo <<<TOP
/* Extension */
#include "php.h"
#include "koda_helper.h"
#include "{$path}.h"

zend_class_entry *ce_{$name};
zend_object_handlers handlers_{$name};

BEGIN_EXTERN_C();

TOP;
        if($class->methods) {
            $method_table = [];
            foreach($class->methods as $method) {
                $method_table[] = "ZEND_ME({$name}, {$method->short}, arginfo_{$method->short}, {$this->_meFlags($method)})";
                if($method->arguments) {
                    $arginfo     = [];
                    foreach($method->arguments as $argument) {
                        $arginfo[] = $this->_arginfo($argument)." // {$argument->dump()}";
                    }
                    $arginfo = "\n    ".implode("\n    ", $arginfo);
                } else {
                    $arginfo = "";
                }
                echo <<<METHOD

/* proto {$method->dump()} */
PHP_METHOD({$name}, {$method->short}) {
    // coming soon
}

ZEND_BEGIN_ARG_INFO_EX(arginfo_{$method->short}, 0, {$method->isReturnRef()}, {$method->required}){$arginfo}
ZEND_END_ARG_INFO();


METHOD;
            }
            $method_table = implode("\n    ", $method_table);
        } else {
            $method_table = "";
        }
        echo <<<REGISTER_METHODS
/* Register methods */
static const zend_function_entry {$name}_methods[] = {
    {$method_table}
    {NULL, NULL, NULL}
};

REGISTER_METHODS;

        if($class->constants) {
            $constants = ["/* Class constants */"];
            foreach($class->constants as $constant) {
                $constants[] = "/* {$constant->dump()} */";
                $constants[] = $this->_constant($constant);
            }
            $constants = implode("\n    ", $constants)."\n";
        } else {
            $constants = "";
        }
        if($class->properties) {
            $properties = ["/* Class properties */"];
            foreach($class->properties as $prop) {
                $properties[] = "/* {$prop->dump()} */";
                $properties[] = $this->_property($prop);
            }
            $properties = implode("\n    ", $properties)."\n";
        } else {
            $properties = "";
        }
        $register = $inherit = [];
        if($class->flags & Flags::IS_INTERFACE) {
            $register[] = "ce_{$name} = zend_register_internal_interface(&ce TSRMLS_CC);";
        } else {
            $register[] = "ce_{$name} = zend_register_internal_class(&ce TSRMLS_CC);";
            if($class->parent) {
                $inherit[] = "if(!kd_extend_class(ce_{$name} TSRMLS_CC, {$class->parent->quote('strtolower')})) {";
                $inherit[] = "    zend_error(E_CORE_ERROR, \"{$this->project->name}: class {$class->escaped} can't extends class {$class->parent->escaped}: class {$class->parent->escaped} not found\");";
                $inherit[] = "    return FAILURE;";
                $inherit[] = "}";
            } else {
                $register[] = "memcpy(&handlers_{$name}, zend_get_std_object_handlers(), sizeof(zend_object_handlers));";
            }
        }
        if($class->interfaces) {
            $interfaces = implode('", "', array_map('strtolower', array_map('addslashes', array_keys($class->interfaces))));
            $inherit[] = "kd_implements_class(ce_{$name} TSRMLS_CC, ".count($class->interfaces).", \"{$interfaces}\");";
        }
        $register = implode("\n    ", $register);
        $inherit = implode("\n    ", $inherit);

        echo <<<REGISTER_CLASS

/* Init class */
PHP_MINIT_FUNCTION(init_{$name}) {
    zend_class_entry ce;

    /* Init class entry */
    INIT_CLASS_ENTRY(ce, "{$escaped}", {$name}_methods);
    {$register}

    {$constants}
    {$properties}
    return SUCCESS;
}

/* Extending and implementing */
PHP_MINIT_FUNCTION(load_{$name}) {
    {$inherit}
    return SUCCESS;
}

END_EXTERN_C();

REGISTER_CLASS;
        return ob_get_clean();
    }

    /**
     * Calculate flags for method entry
     * @param EntityMethod $method
     * @return string
     */
    private function _meFlags(EntityMethod $method) {
        static $names = [
            "__construct" => "ZEND_ACC_CTOR",
            "__destruct"  => "ZEND_ACC_DTOR",
            "__clone"     => "ZEND_ACC_CLONE"
        ];
        static $marks = [
            Flags::IS_STATIC     => "ZEND_ACC_STATIC",
            Flags::IS_ABSTRACT   => "ZEND_ACC_ABSTRACT",
            Flags::IS_FINAL      => "ZEND_ACC_FINAL",
            Flags::IS_PUBLIC     => "ZEND_ACC_PUBLIC",
            Flags::IS_PROTECTED  => "ZEND_ACC_PROTECTED",
            Flags::IS_PRIVATE    => "ZEND_ACC_PRIVATE",
            Flags::IS_DEPRECATED => "ZEND_ACC_DEPRECATED"
        ];
        $flags = [];
        if(isset($names[$method->short])) {
            $flags[] = $names[$method->short];
        }
        foreach($marks as $mark => $flag) {
            if($method->flags & $mark) {
                $flags[] = $flag;
            }
        }
        return implode(" | ", $flags);
    }

} 