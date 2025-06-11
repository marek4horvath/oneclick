#!/bin/bash

cwd=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )

while [[ -f "/bin/entrypoint.sh" ]] && [[ ! -f "/system-is-ready" ]]; do
   echo "Waiting to initialization process."
   sleep 2
done

php_cmd=`which php8.2`
if [[ $? != 0 ]]; then
  php_cmd=`which php`
fi

if [[ -f "composer.phar" ]]; then
  $php_cmd composer.phar install
else
  comp=`which composer`
  $comp install
fi

$cwd/console doctrine:migrations:migrate --no-interaction

if [ ! -f "$cwd/../config/jwt/private.pem" ]; then
    $cwd/console lexik:jwt:generate-keypair
fi

