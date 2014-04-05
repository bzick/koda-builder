dnl Koda compiler, 2014-04-05 13:17:30.

PHP_ARG_WITH(koda_sandbox, for koda/sandbox support,
[  --with-koda-sandbox             Include koda/sandbox support])
PHP_ARG_ENABLE(koda-sandbox-debug, whether to enable debugging support in koda/sandbox,
[  --enable-koda-sandbox-debug     Enable debugging support in koda/sandbox], no, no)

if test "$PHP_KODA_SANDBOX_DEBUG" != "no"; then
    AC_DEFINE(KODA_SANDBOX_DEBUG, 1, [Include debugging support in koda/sandbox])
    AC_DEFINE(KODA_DEBUG, 1, [Include koda debugging])
    CFLAGS="$CFLAGS -Wall -g3 -ggdb -O0"
else
dnl todo: remove this
    CFLAGS="$CFLAGS -Wall -g3 -ggdb -O0"
fi

if test "$PHP_KODA_SANDBOX" != "no"; then
    PHP_ADD_INCLUDE(.)
    PHP_SUBST(KODA_SANDBOX_SHARED_LIBADD)
    PHP_NEW_EXTENSION(koda_sandbox, koda_helper.c php_koda_sandbox.c Koda/Sandbox/Names.c Koda/Sandbox/NamesInterface.c KodaSandbox/Names.c, $ext_shared,, $CFLAGS)
fi