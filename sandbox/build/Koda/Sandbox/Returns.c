/* Extension */
#include "php.h"
#include "koda_helper.h"
#include "Koda/Sandbox/Returns.h"

zend_class_entry *ce_Koda_Sandbox_Returns;
zend_object_handlers handlers_Koda_Sandbox_Returns;

BEGIN_EXTERN_C();

/* proto function Koda\Sandbox\Returns::returnInt():void  [public] */
PHP_METHOD(Koda_Sandbox_Returns, returnInt) {
    RETURN_LONG(5);
}

ZEND_BEGIN_ARG_INFO_EX(arginfo_returnInt, 0, 0, 0)
ZEND_END_ARG_INFO();

/* Register methods */
static const zend_function_entry Koda_Sandbox_Returns_methods[] = {
    ZEND_ME(Koda_Sandbox_Returns, returnInt, arginfo_returnInt, ZEND_ACC_PUBLIC)
    {NULL, NULL, NULL}
};

/* Init class */
PHP_MINIT_FUNCTION(init_Koda_Sandbox_Returns) {
    zend_class_entry ce;

    /* Init class entry */
    INIT_CLASS_ENTRY(ce, "Koda\\Sandbox\\Returns", Koda_Sandbox_Returns_methods);
    ce_Koda_Sandbox_Returns = zend_register_internal_class(&ce TSRMLS_CC);
    memcpy(&handlers_Koda_Sandbox_Returns, zend_get_std_object_handlers(), sizeof(zend_object_handlers));
    
    
    return SUCCESS;
}

/* Extending and implementing */
PHP_MINIT_FUNCTION(load_Koda_Sandbox_Returns) {
    
    return SUCCESS;
}

END_EXTERN_C();
