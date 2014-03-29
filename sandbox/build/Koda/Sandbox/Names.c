/* Extension */
#include "php.h"
#include "koda_helper.h"
#include "Koda/Sandbox/Names.h"

zend_class_entry *ce_Koda_Sandbox_Names;
zend_object_handlers handlers_Koda_Sandbox_Names;

/* proto method Koda\Sandbox\Names::__construct(Koda\Sandbox\Names $self, array $list = NULL):void  [public] */
PHP_METHOD(Koda_Sandbox_Names, __construct) {
    // coming soon
}

ZEND_BEGIN_ARG_INFO_EX(arginfo___construct, 0, 0, 1)
    ZEND_ARG_OBJ_INFO(0, self, "Koda\\Sandbox\\Names", 0) // Koda\Sandbox\Names $self
    ZEND_ARG_ARRAY_INFO(0, list, 1) // array $list = NULL
ZEND_END_ARG_INFO();


/* proto method Koda\Sandbox\Names::publicStatic():void  [static public] */
PHP_METHOD(Koda_Sandbox_Names, publicStatic) {
    // coming soon
}

ZEND_BEGIN_ARG_INFO_EX(arginfo_publicStatic, 0, 0, 0)
ZEND_END_ARG_INFO();


/* proto method Koda\Sandbox\Names::privateStatic():void  [static private] */
PHP_METHOD(Koda_Sandbox_Names, privateStatic) {
    // coming soon
}

ZEND_BEGIN_ARG_INFO_EX(arginfo_privateStatic, 0, 0, 0)
ZEND_END_ARG_INFO();


/* proto method Koda\Sandbox\Names::protectedStatic():void  [static protected] */
PHP_METHOD(Koda_Sandbox_Names, protectedStatic) {
    // coming soon
}

ZEND_BEGIN_ARG_INFO_EX(arginfo_protectedStatic, 0, 0, 0)
ZEND_END_ARG_INFO();


/* proto method Koda\Sandbox\Names::publicMethod():void  [public] */
PHP_METHOD(Koda_Sandbox_Names, publicMethod) {
    // coming soon
}

ZEND_BEGIN_ARG_INFO_EX(arginfo_publicMethod, 0, 0, 0)
ZEND_END_ARG_INFO();


/* proto method Koda\Sandbox\Names::privateMethod():void  [private] */
PHP_METHOD(Koda_Sandbox_Names, privateMethod) {
    // coming soon
}

ZEND_BEGIN_ARG_INFO_EX(arginfo_privateMethod, 0, 0, 0)
ZEND_END_ARG_INFO();


/* proto method Koda\Sandbox\Names::protectedMethod():void  [protected] */
PHP_METHOD(Koda_Sandbox_Names, protectedMethod) {
    // coming soon
}

ZEND_BEGIN_ARG_INFO_EX(arginfo_protectedMethod, 0, 0, 0)
ZEND_END_ARG_INFO();


/* proto method Koda\Sandbox\Names::__clone():void  [public] */
PHP_METHOD(Koda_Sandbox_Names, __clone) {
    // coming soon
}

ZEND_BEGIN_ARG_INFO_EX(arginfo___clone, 0, 0, 0)
ZEND_END_ARG_INFO();


/* proto method Koda\Sandbox\Names::__destruct():void  [public] */
PHP_METHOD(Koda_Sandbox_Names, __destruct) {
    // coming soon
}

ZEND_BEGIN_ARG_INFO_EX(arginfo___destruct, 0, 0, 0)
ZEND_END_ARG_INFO();

/* Register methods */
static const zend_function_entry Koda_Sandbox_Names_methods[] = {
    ZEND_ME(Koda_Sandbox_Names, __construct, arginfo___construct, ZEND_ACC_CTOR | ZEND_ACC_PUBLIC)
    ZEND_ME(Koda_Sandbox_Names, publicStatic, arginfo_publicStatic, ZEND_ACC_STATIC | ZEND_ACC_PUBLIC)
    ZEND_ME(Koda_Sandbox_Names, privateStatic, arginfo_privateStatic, ZEND_ACC_STATIC | ZEND_ACC_PRIVATE)
    ZEND_ME(Koda_Sandbox_Names, protectedStatic, arginfo_protectedStatic, ZEND_ACC_STATIC | ZEND_ACC_PROTECTED)
    ZEND_ME(Koda_Sandbox_Names, publicMethod, arginfo_publicMethod, ZEND_ACC_PUBLIC)
    ZEND_ME(Koda_Sandbox_Names, privateMethod, arginfo_privateMethod, ZEND_ACC_PRIVATE)
    ZEND_ME(Koda_Sandbox_Names, protectedMethod, arginfo_protectedMethod, ZEND_ACC_PROTECTED)
    ZEND_ME(Koda_Sandbox_Names, __clone, arginfo___clone, ZEND_ACC_CLONE | ZEND_ACC_PUBLIC)
    ZEND_ME(Koda_Sandbox_Names, __destruct, arginfo___destruct, ZEND_ACC_DTOR | ZEND_ACC_PUBLIC)
    {NULL, NULL, NULL}
};

/* Init class */
PHP_MINIT_FUNCTION(Koda_Sandbox_Names) {
    zend_class_entry ce;

    /* Init class entry */
    INIT_CLASS_ENTRY(ce, "Koda\\Sandbox\\Names", Koda_Sandbox_Names_methods);
    ce_Koda_Sandbox_Names = zend_register_internal_class(&ce TSRMLS_CC);
    memcpy(&handlers_Koda_Sandbox_Names, zend_get_std_object_handlers(), sizeof(zend_object_handlers));

    /* Class constants */
    /* const Koda\Sandbox\Names::FIVE = 5 */
    REGISTER_CLASS_LONG_CONSTANT(ce_Koda_Sandbox_Names, "FIVE", 5);
    /* const Koda\Sandbox\Names::FLOAT_FIVE = 5.5 */
    REGISTER_CLASS_DOUBLE_CONSTANT(ce_Koda_Sandbox_Names, "FLOAT_FIVE", 5.5);
    /* const Koda\Sandbox\Names::STRING_FIVE = 'five' */
    REGISTER_CLASS_STRING_CONSTANT(ce_Koda_Sandbox_Names, "STRING_FIVE", "five");

    return SUCCESS;
}
