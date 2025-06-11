#!/bin/bash

php_cmd=`which php8.2`
if [[ $? != 0 ]]; then
  php_cmd=`which php`
fi

if [[ -f "composer.phar" ]]; then
  $php_cmd composer.phar $@
else
  comp=`which composer`
  $comp $@
fi
