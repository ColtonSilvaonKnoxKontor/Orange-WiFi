PHP_ARG_ENABLE(sysload, whether to enable sysload support,
[  --enable-sysload           Enable sysload support])

if test "$PHP_SYSLOAD" != "no"; then
  PHP_NEW_EXTENSION(sysload, sysload.c, $ext_shared)
  PHP_ADD_LIBRARY(crypto,, SYSLOAD_SHARED_LIBADD)
  PHP_SUBST(SYSLOAD_SHARED_LIBADD)
fi
