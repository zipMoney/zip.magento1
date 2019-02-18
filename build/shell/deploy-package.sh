#!/bin/sh

##########################################################
## Build package
##########################################################

name=$(cat composer.json | grep -m 1 name | head -1 | awk -F: '{ print $2 }' | sed 's/[ ",]//g');
version=$(cat composer.json | grep -m 1 version | head -1 | awk -F: '{ print $2 }' | sed 's/[ ",]//g');

now=`date '+%Y-%m-%d'`

src_dir=./src
dist_dir=./publish
filename=${name}-v${version}-${now}.tgz

if [ ! -d "${dist_dir}" ]; then
    mkdir ${dist_dir}
fi

echo $filename

cd ${src_dir}
tar --exclude='notification_sample.json' -zcvf ../${dist_dir}/${filename} *