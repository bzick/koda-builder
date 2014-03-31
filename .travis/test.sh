#!/bin/sh -e
echo "Testing Koda"
vendor/bin/phpunit
echo "Generate extension code"
php sandbox/koda.php
echo "Build extension"
cd sandbox/build
phpize
./configure --with-koda-sandbox --enable-koda-sandbox-debug
make
echo "Testing extension"
cd ../..
php -dextension=sandbox/build/modules/koda_sandbox.so vendor/bin/phpunit -c sandbox/phpunit.xml.dist