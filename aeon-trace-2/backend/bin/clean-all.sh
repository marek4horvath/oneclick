#!/bin/bash
cwd=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )
app_dir="${cwd}/.."

rm -rf ${app_dir}/vendor
rm -rf ${app_dir}/node_modules
rm -rf ${app_dir}/public/assets
rm -rf ${app_dir}/public/build
rm -rf ${app_dir}/public/bundles
rm -rf ${app_dir}/public/media
rm -rf ${app_dir}/var
rm -rf ${app_dir}/composer-temp.phar
