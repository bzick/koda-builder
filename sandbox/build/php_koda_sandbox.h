#ifndef PHP_KODA_SANDBOX_H
#define PHP_KODA_SANDBOX_H

extern zend_module_entry koda_sandbox_module_entry;
#define phpext_koda_sandbox_ptr &koda_sandbox_module_entry

#define PHP_KODA_SANDBOX_VERSION "0.3"

#ifdef ZTS
#  include "TSRM.h"
#endif

/* Global functions */
PHP_FUNCTION(php_simple_function);


/* Std module functions */
PHP_MINIT_FUNCTION(koda_sandbox);
PHP_MINIT_FUNCTION(init_Koda_Sandbox_Names); // init class Koda\Sandbox\Names
PHP_MINIT_FUNCTION(load_Koda_Sandbox_Names); // load class Koda\Sandbox\Names

PHP_MINFO_FUNCTION(koda_sandbox);
#endif	/* PHP_KODA_SANDBOX_H */
