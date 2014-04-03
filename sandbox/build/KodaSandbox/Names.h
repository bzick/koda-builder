#ifndef PHP_KODASANDBOX_NAMES_H
#define PHP_KODASANDBOX_NAMES_H

BEGIN_EXTERN_C();

/* Declare class entry */
extern zend_class_entry *ce_KodaSandbox_Names;

/* Methods */
PHP_METHOD(KodaSandbox_Names, abstractMethod);

/* Init function */
PHP_MINIT_FUNCTION(KodaSandbox_Names);

END_EXTERN_C();

#endif	/* PHP_KODASANDBOX_NAMES_H */
