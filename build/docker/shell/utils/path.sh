#!/bin/bash

##########################################################
## Helper functions for Path
##########################################################

fullPath() {

    if [[ "$(machine)" == "Windows" ]]; then
        path=$(echo "$PWD" | sed -e "s/\(\/\)/\\\\/g" -e "s/^\\\\\([a-z]\)/\1:/g")
    else
        path=$PWD
    fi

    echo "$path"

}

getFileExtension() {

    extesion=$(echo "$1" | awk -F'[.]' '{print $(NF-1)"."$NF}')

    case $extesion in
        *gz*)
            echo "tar.gz"
            ;;
        *bz2*)
            echo "tar.bz2"
            ;;
        *zip*)
            echo "zip"
            ;;
        *)
            echo $extesion
    esac

}