#ifndef PHP_KODA_HELPER_H
#define PHP_KODA_HELPER_H

//BEGIN_EXTERN_C();

#define STARTUP_MODULE(module) \
    ZEND_MODULE_STARTUP_N(module)(INIT_FUNC_ARGS_PASSTHRU)

/* Register class constants */
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
    zend_declare_class_constant_null(class_entry, name, sizeof(name)-1, TSRMLS_CC)

/* Register class properties */
#define REGISTER_CLASS_BOOL_PROPERTY(class_entry, name, value, flags)   \
    zend_declare_property_long(class_entry, name, sizeof(name)-1, (long)value, flags TSRMLS_CC)

#define REGISTER_CLASS_LONG_PROPERTY(class_entry, name, value, flags)   \
    zend_declare_property_long(class_entry, name, sizeof(name)-1, (long)value, flags TSRMLS_CC)

#define REGISTER_CLASS_DOUBLE_PROPERTY(class_entry, name, value, flags)   \
    zend_declare_property_long(class_entry, name, sizeof(name)-1, (double)value, flags TSRMLS_CC)

#define REGISTER_CLASS_STRINGL_PROPERTY(class_entry, name, length, value, flags)   \
    zend_declare_property_string(class_entry, name, length, value, length, flags TSRMLS_CC)

#define REGISTER_CLASS_STRING_PROPERTY(class_entry, name, value, flags)   \
    zend_declare_property_string(class_entry, name, sizeof(name)-1, value, flags TSRMLS_CC)

#define REGISTER_CLASS_NULL_PROPERTY(class_entry, name, flags)   \
    zend_declare_property_null(class_entry, name, sizeof(name)-1, flags TSRMLS_CC)
// todo
#define REGISTER_CLASS_ARRAY_PROPERTY(class_entry, name, value, flags)

/**
 * Return zend_class_entry by class name
 * @param const char *class_name class name in lowercase
 * @return zend_class_entry
 **/
extern zend_class_entry *kd_get_class_entry(const char *class_name);

/**
 * Extends class by parent's name
 * @param zend_class_entry *ce
 * @param const char *parent_name parent class name in lowercase
 * @return zend_class_entry parent class entry
 **/
extern zend_class_entry *kd_extend_class(zend_class_entry *ce TSRMLS_DC, const char *parent_name);

/**
 * Multiple implements
 * @param zend_class_entry *ce
 * @param int num_interfaces count of interfaces
 * @param const char* ... names of interfaces
 * @return int FAILURE or SUCCESS
 **/
extern int kd_implements_class(zend_class_entry *ce TSRMLS_DC,  int num_interfaces, ...);

//END_EXTERN_C();

#endif	/* PHP_KODA_HELPER_H */