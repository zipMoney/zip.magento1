#!/bin/sh

##########################################################
## Project Entrypoint
##########################################################

echo "Initaliazing the project..."

sh ./init/install.sh
sh ./init/plugin.sh
sh ./init/clean.sh
