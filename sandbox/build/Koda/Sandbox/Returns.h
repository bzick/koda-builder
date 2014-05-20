#ifndef PHP_KODA_SANDBOX_RETURNS_H
#define PHP_KODA_SANDBOX_RETURNS_H

BEGIN_EXTERN_C();

/* Declare class entry */
extern zend_class_entry *ce_Koda_Sandbox_Returns;

/* Methods */
PHP_METHOD(Koda_Sandbox_Returns, returnInt);

/* Init function */
PHP_MINIT_FUNCTION(init_Koda_Sandbox_Returns);
PHP_MINIT_FUNCTION(load_Koda_Sandbox_Returns);

END_EXTERN_C();

#endif	/* PHP_KODA_SANDBOX_RETURNS_H */
