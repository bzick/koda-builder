#ifndef PHP_KODA_HELPER_H
#define PHP_KODA_HELPER_H

BEGIN_EXTERN_C();

#define REGISTER_CLASS_BOOL_CONSTANT(class_entry, name, value)   \
    zend_declare_class_constant_long(class_entry, name, sizeof(name)-1, (long)value TSRMLS_CC)

#define REGISTER_CLASS_LONG_CONSTANT(class_entry, name, value)   \
    zend_declare_class_constant_long(class_entry, name, sizeof(name)-1, (long)value TSRMLS_CC)

#define REGISTER_CLASS_DOUBLE_CONSTANT(class_entry, name, value)   \
    zend_declare_class_constant_long(class_entry, name, sizeof(name)-1, (double)value TSRMLS_CC)

#define REGISTER_CLASS_STRINGL_CONSTANT(class_entry, name, length, value)   \
    zend_declare_class_constant_string(class_entry, name, length, value, length TSRMLS_CC)

#define REGISTER_CLASS_STRING_CONSTANT(class_entry, name, value)   \
    zend_declare_class_constant_string(class_entry, name, sizeof(name)-1, value TSRMLS_CC)

#define REGISTER_CLASS_NULL_CONSTANT(class_entry, name)   \
    zend_declare_class_constant_long(class_entry, name, sizeof(name)-1, TSRMLS_CC)

END_EXTERN_C();

#endif	/* PHP_KODA_HELPER_H */