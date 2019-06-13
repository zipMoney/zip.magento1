#!/bin/bash

##########################################################
## Main Entry of Build
##########################################################

DOCKER_DIR='./build/docker'

export APP_FRAMEWORK=$1
export APP_ENV=${2:-local}

# load utils modules
source ${DOCKER_DIR}/shell/utils/log.sh
source ${DOCKER_DIR}/shell/utils/machine.sh
source ${DOCKER_DIR}/shell/utils/path.sh
source ${DOCKER_DIR}/shell/utils/symlink.sh

source ${DOCKER_DIR}/shell/utils/docker.sh
## source ${DOCKER_DIR}/shell/utils/host.sh
source ${DOCKER_DIR}/shell/utils/browse.sh

# load environment variables
source ${DOCKER_DIR}/shell/helpers/env.sh
log SUCCESS "Environment variables are loaded successfully"

log INFO "Start to download sources..."
# get source

source ${DOCKER_DIR}/shell/helpers/source.sh

# add host into host file
# addHost ${APP_HOST}

# run docker compose
log INFO "Start to run Docker containers..."
# docker-compose -f ${DOCKER_DIR}/docker-compose.app.yml config
docker-compose -f ${DOCKER_DIR}/docker-compose.app.yml up --build -d --quiet-pull
log SUCCESS "Docker containers are running now."

# waiting until application has been initalized
log WAITING "Waiting for server ready..."
docker_container_wait ${APP_FRAMEWORK}-server "running" 60
log SUCCESS "Server is ready now." 3

log WAITING "Installing application..."
docker_container_wait ${APP_FRAMEWORK}-installer "exited" 300
log SUCCESS "Application is ready now." 3

# open the url on browser
log INFO "Opening ${APP_URL} in your browser..."
openUrl ${APP_URL} 10
