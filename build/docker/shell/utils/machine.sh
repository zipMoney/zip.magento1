#!/bin/bash

##########################################################
## Machine / Operation System
##########################################################

# Detect current machine's OS
function machine() {

    unameOut="$(uname -s)"

    case "${unameOut}" in
        Linux*)     
            machine=Linux
            ;;
        Darwin*)
            machine=Mac
            ;;
        CYGWIN*)    
            machine=Cygwin
            ;;
        MINGW*)     
            machine=MinGw
            ;;
        MSYS*)  
            machine=Windows
            ;;
        *)          
            machine="UNKNOWN:${unameOut}"
    esac

    echo "$machine"

}