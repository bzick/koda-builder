dnl Koda compiler, 2014-03-31 18:44:40.

PHP_ARG_WITH(koda_sandbox, for koda/sandbox support,
[  --with-koda-sandbox             Include koda/sandbox support])
PHP_ARG_ENABLE(koda-sandbox-debug, whether to enable debugging support in koda/sandbox,
[  --enable-koda-sandbox-debug     Enable debugging support in koda/sandbox], no, no)

CFLAGS="$CFLAGS -Wall -g3 -ggdb -O0"

if test "$PHP_KODA_SANDBOX" != "no"; then
    PHP_ADD_INCLUDE(.)
    PHP_SUBST(KODA_SANDBOX_SHARED_LIBADD)
    PHP_NEW_EXTENSION(koda_sandbox, koda_helper.c php_koda_sandbox.c Koda/Sandbox/Names.c, $ext_shared,, $CFLAGS)
fi