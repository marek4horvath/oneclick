#!/bin/sh

cwd=`dirname -- "$0"`

yarn_cmd=`which yarn`
if [ $? != 0 ]; then
  echo "yarn is not installed!"
  exit 255
fi

$yarn_cmd install
$yarn_cmd build
