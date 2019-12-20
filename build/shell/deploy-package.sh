#!/bin/sh

##########################################################
## Create Deploy Package
##########################################################
COMPOSER_FILE='composer.json'
# get release version
RELEASE_VERSION=$(cat $COMPOSER_FILE | grep version | head -1 | awk -F: '{ print $2 }' | sed 's/[ ",]//g');

git archive --format=tar.gz --worktree-attributes HEAD:src/ > publish/Zip-Payment-${RELEASE_VERSION}.tgz
