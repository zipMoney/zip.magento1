#!/bin/sh

##########################################################
## Create Deploy Package
##########################################################
FILE_NAME='composer.json'
# get release version
RELEASE_VERSION=$(cat $FILE_NAME | grep version | head -1 | awk -F: '{ print $2 }' | sed 's/[ ",]//g');

git archive -o publish/Zip-Payment-${RELEASE_VERSION}.zip HEAD:src
