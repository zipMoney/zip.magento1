#!/bin/bash

##########################################################
## Host File
##########################################################

set -eu

# PATH TO YOUR HOSTS FILE
machine="$(machine)"

if [[ "$machine" == "Windows" ]]; then
    ETC_HOSTS="c:\\Windows\\System32\\Drivers\\etc\\hosts"
else
    ETC_HOSTS="/etc/hosts"
fi

if [ ! -f "${DOCKER_DIR}/config/host" ]; then
    createSymlink "${DOCKER_DIR}/config/host" "${ETC_HOSTS}"
fi

# DEFAULT IP FOR HOSTNAME
DEFAULT_IP="127.0.0.1"

# remove domain from host file
function removeHost() {
    local HOSTNAME=$1
    local HOST_REGEX="\(\s\+\)${HOSTNAME}\s*$"
    local HOST_LINE="$(grep -e "${HOST_REGEX}" ${ETC_HOSTS})"
    
    if [ -n "${HOST_LINE}" ]; then
        log INFO "${HOSTNAME} Found in your ${ETC_HOSTS}, Removing now..."
        sed -i -e "s/${HOST_REGEX}/\1/g" -e "/^[^#][0-9\.]\+\s\+$/d" ${ETC_HOSTS}
    else
        log SUCCESS "${HOSTNAME} was not found in your ${ETC_HOSTS}";
    fi
}

# add domain into host file
function addHost() {
    local HOSTNAME=$1
    local IP=${2:-${DEFAULT_IP}}
    
    local HOST_REGEX="\(\s\+\)${HOSTNAME}\s*$"
    local HOST_LINE="$(grep -e "${HOST_REGEX}" ${ETC_HOSTS})"
    
    if [ -n "${HOST_LINE}" ]; then
        log INFO "${HOSTNAME} already exists: \n${HOST_LINE}"   
    else
        log INFO "Adding ${HOSTNAME} to your ${ETC_HOSTS}";
        echo -e "\r\n${IP}\t${HOSTNAME}\n" >> ${ETC_HOSTS}
        HOST_LINE="${IP}\t${HOSTNAME}"
        log SUCCESS "${HOSTNAME} was added succesfully \n${HOST_LINE}";
    fi
}
