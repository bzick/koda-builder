#ifndef PHP_KODA_SANDBOX_NAMES_H
#define PHP_KODA_SANDBOX_NAMES_H

BEGIN_EXTERN_C();

/* Declare class entry */
extern zend_class_entry *ce_Koda_Sandbox_Names;

/* Methods */
PHP_METHOD(Koda_Sandbox_Names, __construct);
PHP_METHOD(Koda_Sandbox_Names, publicStatic);
PHP_METHOD(Koda_Sandbox_Names, privateStatic);
PHP_METHOD(Koda_Sandbox_Names, protectedStatic);
PHP_METHOD(Koda_Sandbox_Names, publicMethod);
PHP_METHOD(Koda_Sandbox_Names, privateMethod);
PHP_METHOD(Koda_Sandbox_Names, protectedMethod);
PHP_METHOD(Koda_Sandbox_Names, __clone);
PHP_METHOD(Koda_Sandbox_Names, __destruct);

/* Init function */
PHP_MINIT_FUNCTION(Koda_Sandbox_Names);

END_EXTERN_C();

#endif	/* PHP_KODA_SANDBOX_NAMES_H */
