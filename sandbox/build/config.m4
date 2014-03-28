dnl Koda compiler, 2014-03-29 02:15:37.

PHP_ARG_WITH(koda_sandbox, for koda/sandbox support,
[  --with-koda_sandbox             Include koda/sandbox support])

CFLAGS="$CFLAGS -Wall -g3 -ggdb -O0"

if test "$PHP_KODA_SANDBOX" != "no"; then
    PHP_ADD_INCLUDE(.)
    PHP_SUBST(KODA_SANDBOX_SHARED_LIBADD)
    PHP_NEW_EXTENSION(koda_sandbox, "php_koda_sandbox.c", $ext_shared,, $CFLAGS)
fi