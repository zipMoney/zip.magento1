#!/bin/bash

##########################################################
## Browser
##########################################################

# Open url on a default browser
function openUrl() {

    local URL=$1
    local attempt_counter=0
    local max_attempts=${2:-5}

    until $(curl --output /dev/null --silent --head --fail ${URL}); do

        if [ ${attempt_counter} -eq ${max_attempts} ];then
            log ERROR "App is not working or timed out.";
            break;
        fi
        
        printf "."
        attempt_counter=$(($attempt_counter+1))
        sleep 2
    done

    machine="$(machine)"

    case "${machine}" in
        Mac*)
            open $URL
            ;;
        Windows*)  
            cmd <<< "start $URL" > /dev/null
            ;;
        *)
    esac

}