#!/bin/bash

# reference: https://misc.flogisoft.com/bash/tip_colors_and_formatting

TEXT_NORMAL=0
TEXT_BOLD=1

TEXT_BLACK=30
TEXT_RED=31
TEXT_GREEN=32
TEXT_YELLOW=33
TEXT_BLUE=34
TEXT_PURPLE=35
TEXT_CYAN=36
TEXT_WHITE=37

BG_BLACK=40
BG_RED=41
BG_GREEN=42
BG_YELLOW=43
BG_BLUE=44
BG_PURPLE=45
BG_CYAN=46
BG_WHITE=47

END_CODE="\033[0m"
OVERWRITE_CODE="\033[0K\\r"

# Print out message on console screen
function log() {
    local TYPE=$1
    local MESSAGE=$2
    local OVERWRITE=${3:-0}

    local line_break_prefix="\n"
    
    # overwrite preivous message
    if [ $OVERWRITE -gt 0 ]; then
        echo -en "\e[${OVERWRITE}A"
        printf "\n"
        echo -en "$(printf %100s | tr " " " ")\r"
        line_break_prefix=""
    fi
    
    case "${TYPE}" in
        INFO*)
            COLOR_START_CODE="\033[${TEXT_NORMAL};${TEXT_CYAN}m"
        ;;
        SUCCESS*)
            COLOR_START_CODE="\033[${TEXT_BOLD};${BG_GREEN}m"
        ;;
        WARNING*)
            COLOR_START_CODE="\033[${TEXT_BOLD};${BG_YELLOW}m"
        ;;
        ERROR*)
            COLOR_START_CODE="\033[${TEXT_BOLD};${BG_RED}m"
        ;;
        WAITING*)
            COLOR_START_CODE="\033[${TEXT_BOLD};${BG_PURPLE}m"
        ;;
        *)
            COLOR_START_CODE="\033[${TEXT_NORMAL};${TEXT_WHITE}m"
        ;;
    esac
    
    case "${TYPE}" in
        SUCCESS* | WARNING* | ERROR* | WAITING*)
            printf "${line_break_prefix}${COLOR_START_CODE} ${TYPE} ${END_CODE} ${MESSAGE}"
        ;;
        *)
            printf "${line_break_prefix}${COLOR_START_CODE}${MESSAGE}${END_CODE}"
        ;;
    esac
    
}