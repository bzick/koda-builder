#ifndef PHP_KODA_SANDBOX_NAMESINTERFACE_H
#define PHP_KODA_SANDBOX_NAMESINTERFACE_H

BEGIN_EXTERN_C();

/* Declare class entry */
extern zend_class_entry *ce_Koda_Sandbox_NamesInterface;

/* Methods */
PHP_METHOD(Koda_Sandbox_NamesInterface, gonnaDo);

/* Init function */
PHP_MINIT_FUNCTION(init_Koda_Sandbox_NamesInterface);
PHP_MINIT_FUNCTION(load_Koda_Sandbox_NamesInterface);

END_EXTERN_C();

#endif	/* PHP_KODA_SANDBOX_NAMESINTERFACE_H */
