#!/bin/bash

##########################################################
## Docker
##########################################################

# wait docker container
function docker_container_wait() {

    local CONTAINER_NAME=$1
    local EXPECTED_STATUS=$2
    local attempt_counter=0
    local timeout=${3:-60} # default 60 seconds
    local sleep_seconds=${4:-2} # default 2 seconds
    local spinner='/-\|'

    status=$(docker inspect -f {{.State.Status}} ${CONTAINER_NAME})

    until [ "$status" == "${EXPECTED_STATUS}" ]; do

        if [ ${attempt_counter} -eq $(($timeout/2)) ]; then
            printf "\n"
            log ERROR "Docker container ${CONTAINER_NAME} is not working or timed out.";
            break;
        fi

        attempt_counter=$(($attempt_counter+1))
        total_seconds=$(($sleep_seconds*$attempt_counter))

        printf "\b%.1s" "$spinner"
        spinner=${spinner#?}${spinner%???}
        echo -en " ${total_seconds}s\r";

        sleep $sleep_seconds
        status=$(docker inspect -f {{.State.Status}} ${CONTAINER_NAME})
    done

    echo -en "$(printf %100s | tr " " " ")\r";
    printf "\n"

}