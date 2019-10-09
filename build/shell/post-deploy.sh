#!/bin/sh

##########################################################
## Post-deployment Shell Script
##########################################################
COMPOSER_FILE='composer.json'

# get release version
RELEASE_VERSION=$(cat $COMPOSER_FILE | grep version | head -1 | awk -F: '{ print $2 }' | sed 's/[ ",]//g');

# Add release tag
git tag -a ${RELEASE_VERSION} -m "Releasing version ${RELEASE_VERSION}"
git push origin ${RELEASE_VERSION}

# create release branch
git branch release/${RELEASE_VERSION}
git push origin release/${RELEASE_VERSION}

# merge master into developer
git checkout master
git merge develop

##########################################################
## Deploy code to the Github
##########################################################

# # curl -u ${GITHUB_USERNAME}:${GITHUB_ACCESS_TOKEN} https://api.github.com/user
# cd src
# git remote add github ${GITHUB_REPO}
# git push github master
# git push github release/${RELEASE_VERSION}
# git push --tags github
