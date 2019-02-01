#!/bin/sh

##########################################################
## Build package
##########################################################

name=$(cat composer.json | grep -m 1 name | head -1 | awk -F: '{ print $2 }' | sed 's/[ ",]//g');
version=$(cat composer.json | grep -m 1 version | head -1 | awk -F: '{ print $2 }' | sed 's/[ ",]//g');

src_dir=./src
dist_dir=./publish
filename=${name}-${version}.tgz

if [ ! -d "${dist_dir}" ]; then
    mkdir ${dist_dir}
fi

cd ${src_dir}
tar -zcvf ../${dist_dir}/${filename} *