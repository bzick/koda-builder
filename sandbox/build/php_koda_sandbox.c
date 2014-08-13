#ifdef HAVE_CONFIG_H
#  include "config.h"
#endif

/* PHP */
#include "php.h"
#include "ext/standard/info.h"

/* Extension */
#include "koda_helper.h"
#include "php_koda_sandbox.h"

BEGIN_EXTERN_C();

#ifdef COMPILE_DL_KODA_SANDBOX
    ZEND_GET_MODULE(koda_sandbox)
#endif
/* Global functions */

/* proto function Koda\Sandbox\simple_multi(double $x, int $y = 5):boolean */
PHP_FUNCTION(simple_multi) {
    
}

ZEND_BEGIN_ARG_INFO_EX(arginfo_simple_multi, 0, 0,  1)
    ZEND_ARG_TYPE_INFO(0, x, IS_DOUBLE, 1) // double $x
    ZEND_ARG_TYPE_INFO(0, y, IS_LONG, 1) // int $y = 5
ZEND_END_ARG_INFO();

/* proto function KodaSandbox\simple_div(double $x, int $y = 5, boolean $allow_zero = false):boolean */
PHP_FUNCTION(simple_div) {
    
}

ZEND_BEGIN_ARG_INFO_EX(arginfo_simple_div, 0, 0,  1)
    ZEND_ARG_TYPE_INFO(0, x, IS_DOUBLE, 1) // double $x
    ZEND_ARG_TYPE_INFO(0, y, IS_LONG, 1) // int $y = 5
    ZEND_ARG_TYPE_INFO(0, allow_zero, IS_BOOL, 1) // boolean $allow_zero = false
ZEND_END_ARG_INFO();

/* Register functions */
const zend_function_entry koda_sandbox_functions[] = {
    ZEND_NS_FE("Koda\\Sandbox", simple_multi, arginfo_simple_multi)
    ZEND_NS_FE("KodaSandbox", simple_div, arginfo_simple_div)
    ZEND_FE_END
};

/* Dependency */
static const zend_module_dep koda_sandbox_depends[] = {
    ZEND_MOD_REQUIRED("tokenizer")
    ZEND_MOD_OPTIONAL("sockets")
    ZEND_MOD_REQUIRED("SPL")
    ZEND_MOD_REQUIRED("json")
    { NULL, NULL, NULL}
};

/* Declare module */
zend_module_entry koda_sandbox_module_entry = {
    STANDARD_MODULE_HEADER_EX,  // api, debug, zts, ...
    NULL,  // ini handler
    koda_sandbox_depends,  // dependencies
    "koda_sandbox",  // human readable module name
    koda_sandbox_functions,  // list of global functions
    PHP_MINIT(koda_sandbox),  // module constructor
    NULL,  // module destructor (auto)
    NULL,  // on start request callback
    NULL,  // on end request callback
    PHP_MINFO(koda_sandbox),  // info for phpinfo()
    "0.2-6-g16c6f54",  // module version
    STANDARD_MODULE_PROPERTIES  // id, flags, ...
};

/* Init module */
PHP_MINIT_FUNCTION(koda_sandbox) {
    /* Constants */
    /* const Koda\Sandbox\FIVE = 5 */
    REGISTER_NS_LONG_CONSTANT("Koda\\Sandbox", "FIVE", 5, CONST_CS | CONST_PERSISTENT);
    /* const Koda\Sandbox\FLOAT_FIVE = 5.5 */
    REGISTER_NS_DOUBLE_CONSTANT("Koda\\Sandbox", "FLOAT_FIVE", 5.5, CONST_CS | CONST_PERSISTENT);
    /* const Koda\Sandbox\STRING_FIVE = 'five' */
    REGISTER_NS_STRING_CONSTANT("Koda\\Sandbox", "STRING_FIVE", "five", CONST_CS | CONST_PERSISTENT);

    /* Classes */
    STARTUP_MODULE(init_Koda_Sandbox_Names); // init Koda\Sandbox\Names
    STARTUP_MODULE(init_Koda_Sandbox_NamesInterface); // init Koda\Sandbox\NamesInterface
    STARTUP_MODULE(init_KodaSandbox_Names); // init KodaSandbox\Names
    STARTUP_MODULE(init_Koda_Sandbox_Returns); // init Koda\Sandbox\Returns
    STARTUP_MODULE(load_Koda_Sandbox_Names); // load Koda\Sandbox\Names
    STARTUP_MODULE(load_Koda_Sandbox_NamesInterface); // load Koda\Sandbox\NamesInterface
    STARTUP_MODULE(load_KodaSandbox_Names); // load KodaSandbox\Names
    STARTUP_MODULE(load_Koda_Sandbox_Returns); // load Koda\Sandbox\Returns

    return SUCCESS;
}

/* Build phpinfo table */
PHP_MINFO_FUNCTION(koda_sandbox) {

    php_info_print_table_start();
    php_info_print_table_header(2, "koda/sandbox support", "enabled");
    php_info_print_table_header(2, "koda/sandbox version", "0.2-6-g16c6f54");
    php_info_print_table_header(2, "koda/sandbox with Koda", "0.1");
#ifdef KODA_SANDBOX_DEBUG
    php_info_print_table_header(2, "koda/sandbox with debug", "yes");
#else
    php_info_print_table_header(2, "koda/sandbox with debug", "no");
#endif
    php_info_print_table_header(2, "koda/sandbox optimization", "none");
    php_info_print_table_end();

}

END_EXTERN_C();