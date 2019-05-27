#!/bin/sh

##########################################################
## Clean
##########################################################

echo "Clean up..."

# clean up cache, session and report storages
rm -rf web/var/cache
rm -rf web/var/session
rm -rf web/var/report

# Flush cache
echo "Flushing cache"
magerun --root-dir="web" cache:flush
