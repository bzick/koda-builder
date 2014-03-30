#include "php.h"
#include "koda_helper.h"

zend_class_entry *kd_get_class_entry(const char *class_name) {
    zend_class_entry **ce;
    if (zend_hash_find(CG(class_table), class_name, strlen(class_name)+1, (void **) &ce)==FAILURE) {
        return NULL;
    } else {
        return *ce;
    }
}

zend_class_entry *kd_extend_class_by_name(zend_class_entry *ce TSRMLS_DC, const char *parent_name) {
    zend_class_entry *parent_ce = kd_get_class_entry(parent_name);
    if(parent_ce) {
        zend_do_inheritance(ce, parent_ce TSRMLS_CC);
        return parent_ce;
    } else {
        return NULL;
    }
}

int kd_implements_class(zend_class_entry *ce TSRMLS_DC,  int num_interfaces, ...) {
    char *interface_name;
    zend_class_entry *interface_entry;
	va_list interface_list;
	va_start(interface_list, num_interfaces);

	while (num_interfaces--) {
		interface_name = va_arg(interface_list, char *);
		interface_entry = kd_get_class_entry(interface_name);
		if(!interface_entry) {
		    return FAILURE;
		}
		zend_do_implement_interface(ce, interface_entry TSRMLS_CC);
	}

	va_end(interface_list);
	return SUCCESS;
}
