#ifndef PHP_KODA_SANDBOX_H
#define PHP_KODA_SANDBOX_H

extern zend_module_entry koda_sandbox_module_entry;
#define phpext_koda_sandbox_ptr &koda_sandbox_module_entry

#define PHP_KODA_SANDBOX_VERSION "0.2-9-g43d8943"

/* Global functions */
PHP_FUNCTION(php_simple_multi);

PHP_FUNCTION(php_simple_div);


/* Std module functions */
PHP_MINIT_FUNCTION(koda_sandbox);
PHP_MINIT_FUNCTION(init_Koda_Sandbox_Names); // init class Koda\Sandbox\Names
PHP_MINIT_FUNCTION(load_Koda_Sandbox_Names); // load class Koda\Sandbox\Names
PHP_MINIT_FUNCTION(init_Koda_Sandbox_NamesInterface); // init class Koda\Sandbox\NamesInterface
PHP_MINIT_FUNCTION(load_Koda_Sandbox_NamesInterface); // load class Koda\Sandbox\NamesInterface
PHP_MINIT_FUNCTION(init_KodaSandbox_Names); // init class KodaSandbox\Names
PHP_MINIT_FUNCTION(load_KodaSandbox_Names); // load class KodaSandbox\Names
PHP_MINIT_FUNCTION(init_Koda_Sandbox_Returns); // init class Koda\Sandbox\Returns
PHP_MINIT_FUNCTION(load_Koda_Sandbox_Returns); // load class Koda\Sandbox\Returns

PHP_MINFO_FUNCTION(koda_sandbox);
#endif	/* PHP_KODA_SANDBOX_H */
