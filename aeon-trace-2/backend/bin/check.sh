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
  comp="${php_cmd} composer.phar"
else
  comp=`which composer`
fi

set -e

echo "Validating Composer files"
$comp validate --strict
echo "Running ECS"
$cwd/../vendor/bin/ecs check --config $cwd/../.ecs/ecs.php
echo "Running PHPStan"
$cwd/../vendor/bin/phpstan analyse -c $cwd/../.phpstan/config.neon --memory-limit 1G
