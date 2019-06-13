#!/bin/sh

##########################################################
## Plugin Installation
##########################################################

WEB_DIR="web"
SOURCE_DIR="source"
PLUGIN_DIR="plugin"
echo "Processing plugins"

IFS=" "
set ${APP_PLUGINS}
for plugin
do
    case $plugin in
        *zip_payment*)

            echo "Installing ${plugin} plugin"

            if [ ! -d "${WEB_DIR}/lib/Zip" ]; then
                mkdir "${WEB_DIR}/lib/Zip"
            fi

            if [ ! -d "${WEB_DIR}/app/code/community/Zip/Payment" ]; then
                mkdir "${WEB_DIR}/app/code/community/Zip/Payment"
            fi

            # Copy plugin code into project
            cp -R ${PLUGIN_DIR}/app ${WEB_DIR}
            cp -R ${PLUGIN_DIR}/js ${WEB_DIR}
            cp -R ${PLUGIN_DIR}/lib ${WEB_DIR}

        ;;
        *)
            echo "no match found in ${plugin}"
    esac

done



# update configurations
echo "Updating plugin configurations"

IFS="|"
set ${PLUGIN_CONFIGURATIONS}
for custom_config
do
    config_key=$(echo "$custom_config" | sed -e "s/=.*\$//")
    config_value=$(echo "$custom_config" | sed -e "s/\b[a-z_\/]*=//1" -e "s/\[\[:space:\]\]/ /g")

    if [ ! -z "$config_key" -a ! -z "$config_value" ]; then
        magerun --root-dir=${WEB_DIR} config:set "${config_key}" "${config_value}"
    fi

done
