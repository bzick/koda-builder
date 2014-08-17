/* Extension */
#include "php.h"
#include "koda_helper.h"
#include "Koda/Sandbox/NamesInterface.h"

zend_class_entry *ce_Koda_Sandbox_NamesInterface;
zend_object_handlers handlers_Koda_Sandbox_NamesInterface;

BEGIN_EXTERN_C();

ZEND_BEGIN_ARG_INFO_EX(arginfo_gonnaDo, 0, 0, 0)
ZEND_END_ARG_INFO();

/* Register methods */
static const zend_function_entry Koda_Sandbox_NamesInterface_methods[] = {
    ZEND_FENTRY(gonnaDo, NULL, arginfo_gonnaDo, ZEND_ACC_ABSTRACT | ZEND_ACC_PUBLIC)
    {NULL, NULL, NULL}
};

/* Init class */
PHP_MINIT_FUNCTION(init_Koda_Sandbox_NamesInterface) {
    zend_class_entry ce;

    /* Init class entry */
    INIT_CLASS_ENTRY(ce, "Koda\\Sandbox\\NamesInterface", Koda_Sandbox_NamesInterface_methods);
    ce_Koda_Sandbox_NamesInterface = zend_register_internal_interface(&ce TSRMLS_CC);
    
    
    return SUCCESS;
}

/* Extending and implementing */
PHP_MINIT_FUNCTION(load_Koda_Sandbox_NamesInterface) {
    if(!kd_extend_class(ce_Koda_Sandbox_NamesInterface TSRMLS_CC, "traversable")) {
        zend_error(E_CORE_ERROR, "koda/sandbox: interface Koda\\Sandbox\\NamesInterface can't extends  Traversable");
        return FAILURE;
    }
    if(!kd_extend_class(ce_Koda_Sandbox_NamesInterface TSRMLS_CC, "iterator")) {
        zend_error(E_CORE_ERROR, "koda/sandbox: interface Koda\\Sandbox\\NamesInterface can't extends  Iterator");
        return FAILURE;
    }
    return SUCCESS;
}

END_EXTERN_C();
