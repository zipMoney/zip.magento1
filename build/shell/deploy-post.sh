#!/bin/sh

##########################################################
## Post-deployment Shell Script
##########################################################

FILE_NAME='composer.json'

# get release version
RELEASE_VERSION_NUMBER=$(cat $FILE_NAME | grep version | head -1 | awk -F: '{ print $2 }' | sed 's/[ ",]//g');
RELEASE_VERSION="v${RELEASE_VERSION_NUMBER}"

# Add release tag
git tag -a ${RELEASE_VERSION} -m "Releasing version ${RELEASE_VERSION}" 
git push origin ${RELEASE_VERSION}

# create release branch
git branch release/${RELEASE_VERSION_NUMBER}
git push origin release/${RELEASE_VERSION_NUMBER}