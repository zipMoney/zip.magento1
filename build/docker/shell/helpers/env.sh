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

# Generate config file to enviornment variables
function loadEnvFromJson() {
    local FILE=$1
    env_configs=($(cat ${FILE} |
            tr -d '\n' |
            grep -o '"[A-Za-z_][A-Za-z_0-9]\+"\s*:\s*\("[^"]\+"\|[0-9\.]\+\|true\|false\|null\)' |
    sed 's/"\(.*\)"\s*:\s*"\?\([^"]\+\)"\?/\1=\2/'))
    
    for env_config in "${env_configs[@]}"
    do
        export $env_config
    done
}

# Load all environment variables for all services
services=( "installer" "server" "platform" "db" )

for service in "${services[@]}"
do
    case $service in
        installer*)
            SERVICE_NAME=${APP_FRAMEWORK}
            ;;
        server*)
            SERVICE_NAME=${SERVER_NAME}
            ;;
        platform*)
            SERVICE_NAME=${PLATFORM_NAME}
            ;;
        db*)
            SERVICE_NAME=${DATABASE_SERVER_NAME}
            ;;
        *)
            log ERROR "Sorry, we can't recoginize this service"
            ;;
    esac

    CONFIG_FILE="${DOCKER_DIR}/config/${service}/${SERVICE_NAME}.yml"

    if [ -f "${CONFIG_FILE}" ]; then
        loadEnvFromYaml ${CONFIG_FILE}
    else
        log ERROR "Can't load ${SERVICE_NAME}'s docker configuration file: ${CONFIG_FILE}"
    fi

done

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