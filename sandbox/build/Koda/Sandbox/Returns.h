#ifndef PHP_KODA_SANDBOX_RETURNS_H
#define PHP_KODA_SANDBOX_RETURNS_H

BEGIN_EXTERN_C();

/* Declare class entry */
extern zend_class_entry *ce_Koda_Sandbox_Returns;

/* Methods */
PHP_METHOD(Koda_Sandbox_Returns, returnInt);
PHP_METHOD(Koda_Sandbox_Returns, returnDouble);
PHP_METHOD(Koda_Sandbox_Returns, returnNegative);
PHP_METHOD(Koda_Sandbox_Returns, returnString);
PHP_METHOD(Koda_Sandbox_Returns, returnTrue);
PHP_METHOD(Koda_Sandbox_Returns, returnFalse);
PHP_METHOD(Koda_Sandbox_Returns, returnNULL);
PHP_METHOD(Koda_Sandbox_Returns, returnVar);

/* Init function */
PHP_MINIT_FUNCTION(init_Koda_Sandbox_Returns);
PHP_MINIT_FUNCTION(load_Koda_Sandbox_Returns);

END_EXTERN_C();

#endif	/* PHP_KODA_SANDBOX_RETURNS_H */
