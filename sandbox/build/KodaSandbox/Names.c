/* Extension */
#include "php.h"
#include "koda_helper.h"
#include "KodaSandbox/Names.h"

zend_class_entry *ce_KodaSandbox_Names;
zend_object_handlers handlers_KodaSandbox_Names;

BEGIN_EXTERN_C();

ZEND_BEGIN_ARG_INFO_EX(arginfo_abstractMethod, 0, 0, 0)
ZEND_END_ARG_INFO();

/* Register methods */
static const zend_function_entry KodaSandbox_Names_methods[] = {
    ZEND_FENTRY(abstractMethod, NULL, arginfo_abstractMethod, ZEND_ACC_ABSTRACT | ZEND_ACC_PUBLIC)
    {NULL, NULL, NULL}
};

/* Init class */
PHP_MINIT_FUNCTION(init_KodaSandbox_Names) {
    zend_class_entry ce;

    /* Init class entry */
    INIT_CLASS_ENTRY(ce, "KodaSandbox\\Names", KodaSandbox_Names_methods);
    ce_KodaSandbox_Names = zend_register_internal_class(&ce TSRMLS_CC);
    memcpy(&handlers_KodaSandbox_Names, zend_get_std_object_handlers(), sizeof(zend_object_handlers));
    ce_KodaSandbox_Names->ce_flags |= ZEND_ACC_EXPLICIT_ABSTRACT_CLASS;
    ce_KodaSandbox_Names->ce_flags |= ZEND_ACC_IMPLICIT_ABSTRACT_CLASS;
    
    
    return SUCCESS;
}

/* Extending and implementing */
PHP_MINIT_FUNCTION(load_KodaSandbox_Names) {
    
    return SUCCESS;
}

END_EXTERN_C();
