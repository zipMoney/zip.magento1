#!/bin/bash

##########################################################
## Environment Variables
##########################################################

source ${DOCKER_DIR}/shell/utils/yaml.sh

# Get framework name and app environment from shell argument
if [ -z "${APP_FRAMEWORK}" ]; then
    log ERROR "No framework provied, please add framework name after your command"
    exit 0
fi

# load config variables from YAML
function loadEnvFromYaml() {
    local yaml_file="$1"
    env_variables=($(parse_yaml $yaml_file && echo))

    for env_variable in "${env_variables[@]}"
    do
        export ${env_variable}
    done
}

# load config file into environments
CONFIG_FILE="${DOCKER_DIR}/docker-config.yml"

if [ -f "${CONFIG_FILE}" ]; then
    loadEnvFromYaml ${CONFIG_FILE}
else
    log ERROR "Can't load ${SERVICE_NAME}'s docker configuration file: ${CONFIG_FILE}"
fi

# set env variables for configruations
export APP_CONFIGURATIONS=${APP_CONFIG[*]}

# App
if [ -z "${APP_HOST}" ]; then
    export APP_HOST=${APP_NAME}.${APP_ENV}
fi

export APP_URL=http://${APP_HOST}:${SERVER_PORT_HTTP}

# Automatically export all variables
set -a
source ${DOCKER_DIR}/env/app.env
source ${DOCKER_DIR}/env/docker.env
set +a
