#!/bin/sh

##########################################################
## Post-deployment Shell Script
##########################################################

# get release version
RELEASE_VERSION=$(cat $FILE_NAME | grep version | head -1 | awk -F: '{ print $2 }' | sed 's/[ ",]//g');

# Add release tag
git tag -a ${RELEASE_VERSION} -m "Releasing version ${RELEASE_VERSION}"
git push origin ${RELEASE_VERSION}

# create release branch
git branch release/${RELEASE_VERSION}
git push origin release/${RELEASE_VERSION}

##########################################################
## Deploy code to the Github
##########################################################

GITHUB_REPO="https://github.com/zipMoney/zip.magento1"

# curl -u ${GITHUB_USERNAME}:${GITHUB_ACCESS_TOKEN} https://api.github.com/user
git remote add github ${GITHUB_REPO}
git push github master
git push github release/${RELEASE_VERSION}
git push --tags github
