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

/* proto function Koda\Sandbox\simple_function(string $x, string $y = 5):bool */
PHP_FUNCTION(simple_function) {
    // coming soon ...
}

ZEND_BEGIN_ARG_INFO_EX(arginfo_simple_function, 0, 0,  1)
    ZEND_ARG_INFO(0, x) // string $x
    ZEND_ARG_INFO(0, y) // string $y = 5
ZEND_END_ARG_INFO();

/* Register functions */
const zend_function_entry koda_sandbox_functions[] = {
    ZEND_NS_FE("Koda\\Sandbox", simple_function, arginfo_simple_function)
    ZEND_FE_END
};

/* Dependency */
static const zend_module_dep koda_sandbox_depends[] = {
    ZEND_MOD_REQUIRED("tokenizer")
    ZEND_MOD_OPTIONAL("sockets")
    ZEND_MOD_REQUIRED("SPL")
    ZEND_MOD_REQUIRED("Core")
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
    "0.3",  // module version
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
    /* Constants */
    STARTUP_MODULE(Koda_Sandbox_Names); // init Koda\Sandbox\Names
    return SUCCESS;
}

/* Build phpinfo table */
PHP_MINFO_FUNCTION(koda_sandbox) {

    php_info_print_table_start();
    php_info_print_table_header(2, "koda/sandbox support", "enabled");
    php_info_print_table_header(2, "koda/sandbox version", "0.3");
    php_info_print_table_header(2, "koda/sandbox with Koda", "0.1");
    php_info_print_table_end();

}

END_EXTERN_C();